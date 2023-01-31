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

/**
 * Repeteadly requests user input until it matches one of the choices.
 * If $choices is empty, then any user input will suffice.
 */
function choice(int $indent, string $prompt, array $choices = []) {
    $prompt = str_repeat(' ', 4 * $indent) . $prompt;
    do {
        $choice = readline($prompt . ' ');
    } while (!empty($choices) && !in_array($choice, $choices));
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
        choice(0, 'Press Enter to continue...');
    }
}

class BenchmarkGrader extends ClassicGrader {
    const JAIL_DIR = IA_ROOT_DIR . 'eval/jail/';

    const ST_OK = 0;    // Test ran in time. The output may or may not be correct.
    const ST_TLE = 1;   // Test exceeded the time limit.
    const ST_OTHER = 2; // Other failures -- killed, memory limit exceeded, etc.

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
     * Runs the job on a single test. Adapted from BaseGrader::grade() and
     * ClassicGrader::testCaseJudge(). Note that, even if a test passed on the
     * old hardware, it may still fail on the new one (e.g. job #552652).
     *
     * Returns an array of:
     *   - status:  one of the ST_* constants;
     *   - time:    relayed from the sandbox, converted to seconds;
     *   - message: relayed from the sandbox.
     **/
    function runTest(array &$test): array {
        eval_assert(clean_dir(self::JAIL_DIR), "Can't clean jail dir.");
        eval_assert(chdir(self::JAIL_DIR), "Can't chdir to jail dir.");
        $infile = $this->getInFile(self::JAIL_DIR);
        $info = $this->runTestCase(
            $test['test_number'],
            self::JAIL_DIR,
            $infile
        );

        if ($info['result'] == 'OK') {
            $status = self::ST_OK;
        } else if (preg_match('/time limit/i', $info['message']))  {
            $status = self::ST_TLE;
        } else {
            $status = self::ST_OTHER;
        }

        return [
            'status' => $status,
            'time' => (float)$info['time'] / 1000,
            'message' => $info['message'],
        ];
    }
}

class TimeInfo {
    public float $old_time;
    public bool $old_tle;
    public float $new_time;
    public bool $new_tle;

    function __construct($old_time, $old_tle, $new_time, $new_tle) {
        $this->old_time = $old_time;
        $this->old_tle = $old_tle;
        $this->new_time = $new_time;
        $this->new_tle = $new_tle;
    }

    /**
     * Given a time limit, maks sure it is above MIN_TIME_LIMIT. Then round it
     * to a multiple of 0.05 for small values or 0.1 for larger values.
     **/
    static function adjust_time_limit(float $t):float {
        $t = max($t, MIN_TIME_LIMIT);

        // Example: 0.33 should be rounded to 0.35. Compute 0.33 * 20 = 6.6,
        // round it to 7.0, then divide it back by 20 to get 3.5.
        $factor = ($t < ROUND_THRESHOLD) ? 20 : 10;
        return round($t * $factor) / $factor;
    }

    /**
     * Tallies what would happen under a new time limit. Returns three values:
     *   - Number of tests that would produce the same outcome.
     *   - Number of tests that used to fail and would now pass.
     *   - Number of tests that used to pass and would now fail.
     **/
    static function tally_changes(float $new_time_limit, array &$times): array {
        $result = [ 0, 0, 0 ];
        foreach ($times as $ti) {
            if ($ti->old_tle == ($ti->new_time >= $new_time_limit)) {
                $result[0]++;
            } else if ($ti->old_tle) {
                $result[1]++;
            } else {
                $result[2]++;
            }
        }
        return $result;
    }

    /**
     * Given an aray of TimeInfo's for every test run, recommends a new time
     * limit suitable for the current hardware. Returns null and prints a
     * warning if it has nothing to recommend.
     **/
    static function recommend_time_limit(float $time_limit, array &$times): ?float {
        if (empty($times)) {
            msg(MSG_WARNING, 1, 'No recommendation: no tests were run.');
            return null;
        } else if ($time_limit <= MIN_TIME_LIMIT) {
            msg(MSG_WARNING, 1, 'No recommendation: time limit is already small.');
            return null;
        }

        // Count the number of TLE's on the old hardware.
        $num_passed = 0;
        foreach ($times as $ti) {
            $num_passed += !$ti->old_tle;
        }

        // Sort the records by new times.
        usort($times, function(TimeInfo $a, TimeInfo $b) {
            return $a->new_time <=> $b->new_time;
        });

        // We want the first $num_passed tests to still pass and the rest to
        // exceed the time limit.
        $new_limit = $num_passed
            ? ($times[$num_passed - 1]->new_time + $times[$num_passed]->new_time) / 2
            : $times[0]->new_time;
        $round_new_limit = self::adjust_time_limit($new_limit);

        if ($round_new_limit >= $time_limit) {
            msg(MSG_WARNING, 1, 'No recommendation: running time did not decrease.');
            return null;
        } else {
            $tally = self::tally_changes($round_new_limit, $times);
            $n = count($times); // syntactic sugar
            msg(MSG_SUCCESS, 1, 'RECOMMENDATION: reduce time limit from %g to %g (rounded from %g).',
                $time_limit, $round_new_limit, $new_limit);
            msg(MSG_SUCCESS, 1, 'Based on %d tests (%d passing, %d failing with TLE).',
                $n, $num_passed, $n - $num_passed);
            msg(MSG_SUCCESS, 1, 'Effect: %d (%0.1f%%) unchanged, %d (%0.1f%%) fail->pass, %d (%0.1f%%) pass->fail.',
                $tally[0], 100 * $tally[0] / $n,
                $tally[1], 100 * $tally[1] / $n,
                $tally[2], 100 * $tally[2] / $n);
            return $round_new_limit;
        }
    }
}

class EvalBenchmark {
    // Checkpoint file names.
    const CP_TASKS = 'checkpoint_tasks.txt';
    const CP_SQL = 'checkpoint.sql';

    // Things we can do with a test case
    const ACTION_IGNORE = 0;    // ignore it
    const ACTION_REPORT = 1;  // report it as inconsistent
    const ACTION_USE = 2;     // use it for benchmarking

    // Due to graders, success messages can be quite baroque.
    // Any tests that scored >= 1 points are assumed to be successful.
    // Partial scores are fine -- we only care about the time limit.
    //
    // The messages below indicate a TLE status.
    const TLE_MESSAGES = [
        'Time limit exceeded',
        'Time limit exceeded.',
        'Wall time limit exceeded',
        'Wall time limit exceeded.',
    ];

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

    function save_checkpoint(array &$task) {
        $task_file = $this->get_tasks_checkpoint_file();
        file_put_contents(
            $task_file,
            $task['id'] . "\n",
            FILE_APPEND);
        msg(MSG_SUCCESS, 1, '✔ Checkpoint saved.');
    }

    function save_sql(array &$task, float $time_limit) {
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
        msg(MSG_SUCCESS, 1, '✔ SQL statement saved.');
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

    function print_task_header(array &$task, int $ord, int $total, float $time_limit) {
        $header = sprintf('| Task %d/%d (%s, %d tests, %g s) |',
                          $ord,
                          $total,
                          $task['id'],
                          $task['test_count'],
                          $time_limit);
        $line = '+' . str_repeat('-', mb_strlen($header) - 2) . '+';

        print "\n";
        msg(MSG_INFO, 0, $line);
        msg(MSG_INFO, 0, $header);
        msg(MSG_INFO, 0, $line);
        print "\n";
    }

    /**
     * Figure out what to do with a test based on its outcome.
     * @return int One of the ACTION_* constants, indicating whether we can
     * use the test, ignore it or report it as inconsistent.
     **/
    function test_action(int $points, float $test_time, float $time_limit,
                         string $grader_message): int {
        $in_time = $test_time < $time_limit;
        $tle_msg = in_array($grader_message, self::TLE_MESSAGES);

        // 8 cases arise from ($points, $in_time, $tle_msg).
        if (($in_time == $tle_msg) ||
            ($points && !$in_time)) {
            // Cases 1-4: The TLE message (or its absence) is inconsistent
            // with the test running in time (or not).
            // Case 5: The test did not run in time yet still got some points.
            return self::ACTION_REPORT;
        } else if (!$points && $in_time) {
            // Case 6: Do nothing. Test got 0 points due to other errors:
            // wrong answer, memory limit exceeded etc.
            return self::ACTION_IGNORE;
        } else {
            // Cases 7-8: TLE or the test got some points.
            return self::ACTION_USE;
        }
    }

    /**
     * Runs all the tests for the job. Returns an array of TimeInfo's.
     **/
    function benchmark_job_tests(
        array &$task, array &$task_params, array &$job, array &$tests): array {

        // Create the grader and compile the job's source.
        // Do not mark the job as pending, do not compile any graders etc.
        $grader = new BenchmarkGrader($task, $task_params, $job);
        $grader->compileJobSource();

        $time_limit = (float)$task_params['timelimit'];
        $times = [];
        foreach ($tests as $test) {
            $test_time = (float)$test['exec_time'] / 1000; // in seconds
            $points = (int)$test['points'];
            $action = $this->test_action(
                $points, $test_time, $time_limit, $test['grader_message']);

            if ($action == self::ACTION_USE) {
                $info = $grader->runTest($test);

                if ($info['status'] == BenchmarkGrader::ST_OTHER) {
                    // Ignore this test case and report why
                    msg(MSG_WARNING,
                        2,
                        'Test #%02d: ignored after grading (old points: %d, old time: %g, old message: %s) (new time: %d, new message: %s)',
                        $test['test_number'],
                        $points,
                        $test_time,
                        $test['grader_message'],
                        $info['time'],
                        $info['message']
                    );
                } else {
                    $old_tle = ($test_time >= $time_limit);
                    $new_tle = ($info['status'] == BenchmarkGrader::ST_TLE);
                    $new_test_time = $info['time'];

                    msg(MSG_DEFAULT, 2, 'Test #%02d: old time %g%s, new time %g%s',
                        $test['test_number'],
                        $test_time,
                        $old_tle ? ' (TLE)' : '',
                        $new_test_time,
                        $new_tle ? ' (TLE)' : '');

                    $times[] = new TimeInfo($test_time, $old_tle, $new_test_time, $new_tle);
                }
            } else {
                $msgClass = ($action == self::ACTION_REPORT) ? MSG_WARNING : MSG_INFO;
                $verdict = ($action == self::ACTION_REPORT) ? 'inconsistent' : 'ignored';
                msg($msgClass,
                    2,
                    'Test #%02d: %s (points: %d, time: %g, grader message: %s)',
                    $test['test_number'],
                    $verdict,
                    $points,
                    $test_time,
                    $test['grader_message']);
            }
        }

        return $times;
    }

    /**
     * Runs all tests for all jobs for this task. Returns an aggregate array
     * of TimeInfo's over all tests.
     */
    function benchmark_task_jobs(array &$task, array &$task_params, array &$jobs): array {
        $times = [];
        foreach ($jobs as $job) {
            $owner = $this->admins[$job['user_id']]['username'];
            $header = sprintf('Job #%d (%s):', $job['id'], $owner);

            $tests = job_test_get_all($job['id']);
            if (count($tests) != $task['test_count']) {
                msg(MSG_WARNING, 1, '%s SKIPPING (task specifies %d tests, job has %d)',
                    $header, $task['test_count'], count($tests));
            } else {
                msg(MSG_DEFAULT, 1, '%s Running %d tests',
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

            // Load jobs submitted by admins.
            $jobs = job_get_by_task_id_user_ids_status(
                $task['id'], array_keys($this->admins), 'done');

            $this->print_task_header($task, $i, count($tasks), $time_limit);

            // Decide whether we have everything we need for this task.
            if (!$time_limit) {
                msg(MSG_WARNING, 1, "SKIPPING (time limit not set)");
            } else if (empty($jobs)) {
                msg(MSG_WARNING, 1, "SKIPPING (no admin jobs)");
            } else if ($task['type'] != 'classic') {
                msg(MSG_WARNING, 1, "SKIPPING (not handling [%s] tasks",
                    $task['type']);
            } else {
                msg(MSG_INFO, 1, "Benchmarking %d jobs", count($jobs));
                $times = $this->benchmark_task_jobs($task, $task_params, $jobs);
                $rec = TimeInfo::recommend_time_limit($time_limit, $times);
                if ($rec) {
                    $choice = choice(1, 'Accept recommendation? [y/n]', ['y', 'n']);
                    if ($choice == 'y') {
                        $this->save_sql($task, $rec);
                    }
                }
            }
            $this->save_checkpoint($task);
            choice(1, 'Press Enter to continue to the next task or Ctrl-C to quit...');
        }
    }
}

$opts = getopt('c:y');
$checkpoint_dir = $opts['c'] ?? null;
$usage_confirmed = isset($opts['y']);

if (!$checkpoint_dir) {
    fatal("Please specify a checkpoint directory with -c <dir>.\n" .
          'This allows you to save/restore progress.');
}
usage($usage_confirmed);

$eb = new EvalBenchmark($checkpoint_dir);
$eb->run();
