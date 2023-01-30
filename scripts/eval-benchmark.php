<?php

require_once __DIR__ . '/../eval/config.php';

require_once(IA_ROOT_DIR.'common/log.php');
require_once(IA_ROOT_DIR.'common/db/job.php');
require_once(IA_ROOT_DIR.'common/db/task_statistics.php');

require_once(IA_ROOT_DIR.'eval/utilities.php');
require_once(IA_ROOT_DIR.'eval/download.php');
require_once(IA_ROOT_DIR.'eval/Exceptions.php');
require_once(IA_ROOT_DIR.'eval/ClassicGrader.php');

// Only benchmark these users' sources.
const ADMIN_USERNAMES = [ 'francu', 'Catalin.Francu', 'mihai.tutu' ];

// Don't recommend anything if the time limit is already this low.
// If we do recommend something, don't go below this limit.
const MIN_TIME_LIMIT = 0.1;

// Time limit recommendations below this limit will be rounded up to a
// multiple of 0.05. Time limit recommendations above this limit will be
// rounded up to a multiple of 0.1.
const ROUND_THRESHOLD = 0.5;

const MSG_DEFAULT = 0;
const MSG_ERROR = 1;
const MSG_WARNING = 2;
const MSG_SUCCESS = 3;
const MSG_INFO = 4;

const COLORS = [
    MSG_DEFAULT => "\e[39m",
    MSG_ERROR => "\e[91m",
    MSG_WARNING => "\e[93m",
    MSG_SUCCESS => "\e[92m",
    MSG_INFO => "\e[94m",
];

function msg(int $class, int $indent, string $fmt, ...$args) {
    $spaces = str_repeat(' ', 4 * $indent);
    $str = vsprintf($fmt, $args);
    printf("%s%s%s%s\n", $spaces, COLORS[$class], $str, COLORS[MSG_DEFAULT]);
}

function fatal($fmt, ...$args) {
    msg(MSG_ERROR, 0, $fmt, ...$args);
    exit(1);
}

function choice($prompt, $choices) {
  do {
    $choice = readline($prompt . ' ');
  } while (!in_array($choice, $choices));
  return $choice;
}

function usage(bool $confirmed) {

    print <<<END_USAGE
This script will help calibrate the time limits of all tasks when you change the eval
hardware.

More details will follow.


END_USAGE;

    if (exec('whoami') != 'root') {
        fatal('This script MUST be run as root.');
    }

    // Warn about a noisy log level.
    if (IA_ERROR_REPORTING & E_USER_NOTICE) {
        msg(MSG_WARNING, 0, 'We advise changing this value in config.php:');
        msg(MSG_WARNING, 0, "\n    define('IA_ERROR_REPORTING', E_ALL & ~E_USER_NOTICE);\n");
        msg(MSG_WARNING, 0, 'Allowing E_USER_NOTICE will clutter this script\'s log with jail info.');
    }

    if (!$confirmed) {
        readline('Press Enter to continue... ');
    }
}

class BenchmarkGrader extends ClassicGrader {
    const JAIL_DIR = IA_ROOT_DIR . 'eval/jail/';

    function __construct($task, $task_params, $job) {
        // Don't get hung up on memory constraints. They may have to do with
        // 64- versus 32- bit architectures. Just give the program another MB.
        $task_params['memlimit'] += 1024;
        parent::__construct($task, $task_params, $job);
    }

    function compileJobSource() {
        parent::processUserSubmission();
    }

    /**
     * Runs the job on a single test. Returns the running time. Adapted from
     * BaseGrader::grade() and ClassicGrader::testCaseJudge().
     */
    function runTest(array $test): float {
        eval_assert(clean_dir(self::JAIL_DIR), "Can't clean jail dir.");
        eval_assert(chdir(self::JAIL_DIR), "Can't chdir to jail dir.");
        $infile = $this->getInFile(self::JAIL_DIR);
        $result = $this->runTestCase(
            $test['test_number'],
            self::JAIL_DIR,
            $infile
        );

        if ($result['message'] != 'Success') {
            msg(MSG_ERROR, 0, "ERROR: Test case failed!");
            print_r($result);
            exit;
        }
        return $result['time'] / 1000.0;
    }
}

class EvalBenchmark {
    // Checkpoint file names.
    const CP_TASKS = 'checkpoint_tasks.txt';
    const CP_SQL = 'checkpoint.sql';

    private array $admins;
    private string $checkpoint_dir;
    private array $seen_tasks = [];

    function __construct($checkpoint_dir) {
        if (!is_dir($checkpoint_dir)) {
            fatal('Checkpoint directory does not exist or is not a directory.');
        }
        $this->checkpoint_dir = $checkpoint_dir;
        $this->restore_checkpoint();
    }

    function get_tasks_checkpoint_file() {
        return $this->checkpoint_dir . '/' . self::CP_TASKS;
    }

    function get_sql_checkpoint_file() {
        return $this->checkpoint_dir . '/' . self::CP_SQL;
    }

    function restore_checkpoint() {
        $task_file = $this->get_tasks_checkpoint_file();
        if (file_exists($task_file)) {
            $this->seen_tasks = file($task_file, FILE_IGNORE_NEW_LINES);
        } else {
            msg(MSG_WARNING, 0, 'Checkpoint file %s not found. Running new instance.',
                $task_file);
        }
    }

    function save_checkpoint(array $task) {
        $task_file = $this->get_tasks_checkpoint_file();
        file_put_contents(
            $task_file,
            $task['id'] . "\n",
            FILE_APPEND);
    }

    function save_sql(array $task, float $time_limit) {
        $sql_file = $this->get_sql_checkpoint_file();
        $query = sprintf(
            'update ia_parameter_value ' .
            'set value = "%g" ' .
            'where object_type = "task" ' .
            'and object_id = "%s" ' .
            'and parameter_id = "timelimit"',
            $time_limit, $task['id']);
        file_put_contents(
            $sql_file,
            $query . "\n",
            FILE_APPEND);
    }

    /**
     * Returns a map of user_id => user for the users defined in ADMIN_USERNAMES.
     **/
    function load_admins() {
        $this->admins = [];

        foreach (ADMIN_USERNAMES as $username) {
            $user = user_get_by_username($username);
            $user or fatal('Admin "%s" not found.', $username);
            $this->admins[$user['id']] = $user;
        }
    }

    function load_tasks() {
        $tasks = task_get_all();
        $skipped = 0;
        foreach ($tasks as $i => $t) {
            if (in_array($t['id'], $this->seen_tasks)) {
                unset($tasks[$i]);
                $skipped++;
            }
        }

        if ($skipped) {
            printf("Skipping %d checkpointed tasks.\n", $skipped);
        }

        return $tasks;
    }

    /**
     * Given a time limit, maks sure it is above MIN_TIME_LIMIT. Then round it
     * to a multiple of 0.05 for small values or 0.1 for larger values.
     **/
    function adjust_time_limit(float $t):float {
        $t = max($t, MIN_TIME_LIMIT);

        // Example: 0.33 should be rounded to 0.35. Compute 0.33 * 20 = 6.6,
        // round it to 7.0, then divide it back by 20 to get 3.5.
        $factor = ($t < ROUND_THRESHOLD) ? 20 : 10;
        return ceil($t * $factor) / $factor;
    }

    /**
     * Given an aray of (old time, new time) pairs for every test run,
     * recommends a new time limit suitable for the current hardware.
     **/
    function recommend_time_limit(array $task, float $time_limit, array $times) {
        // Compare the old maximum to the new maximum.
        $max_old_time = $max_new_time = 0.0;
        foreach ($times as $pair) {
            $max_old_time = max($max_old_time, $pair[0]);
            $max_new_time = max($max_new_time, $pair[1]);
        }

        // No recommendations if
        //   (1) no tests were run or
        //   (2) the time limit is already low enough or
        //   (3) the new worst time is worse than the old one
        if (empty($times)) {
            msg(MSG_WARNING, 1, "No recommendation: no tests were run.");
        } else if ($time_limit <= MIN_TIME_LIMIT) {
            msg(MSG_WARNING, 1, "No recommendation: time limit is already small.");
        } else if ($max_new_time >= $max_old_time) {
            msg(MSG_WARNING, 1, "No recommendation: old worst time was better.");
        } else {
            $new_limit = $time_limit * $max_new_time / $max_old_time;
            $round_new_limit = $this->adjust_time_limit($new_limit);
            msg(MSG_SUCCESS, 1, 'old worst time = %g, new worst time = %g',
                $max_old_time, $max_new_time);
            msg(MSG_SUCCESS, 1, 'RECOMMENDATION: reduce time limit from %g to %g (rounded from %g)',
                $time_limit, $round_new_limit, $new_limit);
            $choice = choice('Accept recommendation? [y/n]', ['y', 'n']);
            if ($choice == 'y') {
                $this->save_sql($task, $round_new_limit);
            }
        }
    }

    /**
     * Runs all the tests for the job. Returns an array of (old time, new
     * time) pairs.
     **/
    function benchmark_job_tests(
        array $task, array $task_params, array $job, array $tests):array {

        // Create the grader and compile the job's source.
        // Do not mark the job as pending, do not compile any graders etc.
        $grader = new BenchmarkGrader($task, $task_params, $job);
        $grader->compileJobSource();

        $time_limit = $task_params['timelimit'];
        $times = [];
        foreach ($tests as $test) {
            // Normally, this is smaller than $time_limit. It may, however, be
            // larger if someone changed the time limit after the job was
            // evaluated (e.g.: antitir). We print a warning, but we
            // continue. We are interested in comparing the new hardware to
            // the old one.
            $old_test_time = (float)$test['exec_time'] / 1000;
            $new_test_time = $grader->runTest($test);

            $warning = ($old_test_time > $time_limit)
                ? 'WARNING: old time exceeds limit'
                : '';
            msg(MSG_DEFAULT, 2, 'Test #%02d, old time %g, new time %g %s',
                $test['test_number'], $old_test_time, $new_test_time, $warning);

            $times[] = [ $old_test_time, $new_test_time ];
        }

        return $times;
    }

    /**
     * Runs all tests for all jobs for this task. Returns a combined array of
     * (old time, new time) over all tests.
     */
    function benchmark_task_jobs(array $task, array $task_params, array $jobs):array {
        $times = [];
        foreach ($jobs as $job) {
            $owner = $this->admins[$job['user_id']]['username'];
            $header = sprintf('Job #%d (%s): ', $job['id'], $owner);

            $tests = job_test_get_all($job['id']);
            if (count($tests) != $task['test_count']) {
                msg(MSG_WARNING, 1, '%s SKIPPING (task specifies %d tests, job has %d)',
                    $header, $task['test_count'], count($tests));
            } else {
                msg(MSG_DEFAULT, 1, '%s Benchmarking %d tests',
                    $header, count($tests));
                $job_times = $this->benchmark_job_tests(
                    $task, $task_params, $job, $tests);
                array_push($times, ...$job_times);
            }
        }

        return $times;
    }

    function run() {
        db_connect();

        // Load user IDs.
        $this->load_admins();
        $admin_ids = array_keys($this->admins);

        // Load all tasks.
        $tasks = $this->load_tasks();

        foreach ($tasks as $i => $task) {
            // Load task parameters (we only need the time limit).
            $task_params = task_get_parameters($task['id']);
            $time_limit = (float)$task_params['timelimit'];

            // Load jobs submitted by admins and having a score of 100.
            $jobs = job_get_by_task_id_user_ids_status_score(
                $task['id'], array_keys($this->admins), 'done', 100);

            $header = sprintf("== Task %d/%d (%s, %d tests, %g s): ",
                              $i + 1,
                              count($tasks),
                              $task['id'],
                              $task['test_count'],
                              $time_limit);

            // Decide whether we have everything we need for this task.
            if (!$time_limit) {
                msg(MSG_WARNING, 0, "{$header} SKIPPING (time limit not set)");
            } else if (empty($jobs)) {
                msg(MSG_WARNING, 0, "{$header} SKIPPING (no admin jobs)");
            } else if ($task['type'] != 'classic') {
                msg(MSG_WARNING, 0, "%s SKIPPING (not handling [%s] tasks",
                    $header, $task['type']);
            } else {
                msg(MSG_INFO, 0, "%s Benchmarking %d jobs", $header, count($jobs));
                $times = $this->benchmark_task_jobs($task, $task_params, $jobs);
                $this->recommend_time_limit($task, $time_limit, $times);
            }
            $this->save_checkpoint($task);
            readline('Press Enter to continue to the next task... ');
        }
    }
}

$opts = getopt('c:y');
$checkpoint_dir = $opts['c'] ?? null;
$usage_confirmed = isset($opts['y']);

usage($usage_confirmed);

if (!$checkpoint_dir) {
    fatal("Please specify a checkpoint directory with -c <dir>.\n" .
          'This allows you to save/restore progress.');
}

$eb = new EvalBenchmark($checkpoint_dir);
$eb->run();