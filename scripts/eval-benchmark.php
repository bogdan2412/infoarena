<?php

require_once __DIR__ . '/../eval/config.php';

require_once(IA_ROOT_DIR.'common/log.php');
require_once(IA_ROOT_DIR.'common/common.php');

require_once(IA_ROOT_DIR.'common/score.php');
require_once(IA_ROOT_DIR.'common/task.php');
require_once(IA_ROOT_DIR.'common/round.php');
require_once(IA_ROOT_DIR.'common/security.php');
require_once(IA_ROOT_DIR.'common/db/task.php');
require_once(IA_ROOT_DIR.'common/db/job.php');
require_once(IA_ROOT_DIR.'common/db/user.php');
require_once(IA_ROOT_DIR.'common/db/task_statistics.php');

require_once(IA_ROOT_DIR.'eval/utilities.php');
require_once(IA_ROOT_DIR.'eval/download.php');
require_once(IA_ROOT_DIR.'eval/Exceptions.php');
require_once(IA_ROOT_DIR.'eval/ClassicGrader.php');
require_once(IA_ROOT_DIR.'eval/InteractiveGrader.php');

class EvalBenchmark {
    // Only benchmark these users' sources.
    const ADMIN_USERNAMES = [ 'francu', 'Catalin.Francu', 'mihai.tutu' ];

    // Don't recommend anything if the time limit is already low.
    const MIN_TIME_LIMIT = 0.2;

    private array $admins;

    /**
     * Returns a map of user_id => user for the users defined in ADMIN_USERNAMES.
     **/
    function load_admins() {
        $this->admins = [];

        foreach (self::ADMIN_USERNAMES as $username) {
            $user = user_get_by_username($username);
            $user or die("Fatal: Admin \"{$username}\" not found.\n");
            $this->admins[$user['id']] = $user;
        }
    }

    /**
     * Given an aray of (old time, new time) pairs for every test run,
     * recommends a new time limit suitable for the current hardware.
     **/
    function recommend_time_limit(float $time_limit, array $times) {
        $max_old_time = $max_new_time = 0.0;
        foreach ($times as $pair) {
            $max_old_time = max($max_old_time, $pair[0]);
            $max_new_time = max($max_new_time, $pair[1]);
        }

        // No recommendations if
        //   (1) no tests were run or
        //   (2) the time limit is already low enough or
        //   (3) the new worst time is worse than the old one
        if (empty($times) ||
            ($time_limit < self::MIN_TIME_LIMIT) ||
            ($max_new_time >= $max_old_time)) {
            return;
        }

        $new_limit = $time_limit * $max_new_time / $max_old_time;
        printf("  RECOMMENDATION: max_old_time = %0.03f, max_new_time = %0.03f, reduce time limit from %0.02f to %0.02f\n",
               $max_old_time, $max_new_time, $time_limit, $new_limit);
    }

    /**
     * Runs all the tests for the job. Returns an array of (old time, new
     * time) pairs.
     **/
    function benchmark_job_tests(
        array $task, float $time_limit, array $job, array $tests):array {

        $times = [];
        foreach ($tests as $test) {
            // Normally, this is smaller than $time_limit. It may, however, be
            // larger if someone changed the time limit after the job was
            // evaluated (e.g.: antitir). We print a warning, but we
            // continue. We are interested in comparing the new hardware to
            // the old one.
            $old_test_time = (float)$test['exec_time'] / 1000;

            $warning = ($old_test_time > $time_limit)
                ? 'WARNING: TLE'
                : '';
            printf("    Test #%02d, old time %0.03f %s\n",
                   $test['test_number'], $old_test_time, $warning);

            // TODO actually run the test
            $new_test_time = $old_test_time * 0.9;
            printf("      new time %0.03f\n", $new_test_time);

            $times[] = [ $old_test_time, $new_test_time ];
        }

        return $times;
    }

    /**
     * Runs all tests for all jobs for this task. Returns a combined array of
     * (old time, new time) over all tests.
     */
    function benchmark_task_jobs(array $task, float $time_limit, array $jobs):array {
        $times = [];
        foreach ($jobs as $job) {
            $owner = $this->admins[$job['user_id']];
            printf("  Job #%d (%s): ", $job['id'], $owner['username']);

            $tests = job_test_get_all($job['id']);
            if (count($tests) != $task['test_count']) {
                printf("SKIPPING (task specifies %d tests, job has %d)\n",
                       $task['test_count'], count($tests));
            } else {
                printf("Benchmarking %d tests\n", count($tests));
                $job_times = $this->benchmark_job_tests(
                    $task, $time_limit, $job, $tests);
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
        $tasks = task_get_all();
        foreach ($tasks as $i => $task) {
            // Load task parameters (we only need the time limit).
            $task_params = task_get_parameters($task['id']);
            $time_limit = (float)$task_params['timelimit'];

            // Load jobs submitted by admins and having a score of 100.
            $jobs = job_get_by_task_id_user_ids_status_score(
                $task['id'], array_keys($this->admins), 'done', 100);

            printf("== Task %d/%d (%s, %d tests, %0.2f s): ",
                   $i + 1,
                   count($tasks),
                   $task['id'],
                   $task['test_count'],
                   $time_limit);

            // Decide whether we have everything we need for this task.
            if (!$time_limit) {
                printf("SKIPPING (time limit not set)\n");
            } else if (empty($jobs)) {
                printf("SKIPPING (no admin jobs)\n");
            } else {
                printf("Benchmarking %d jobs\n", count($jobs));
                $times = $this->benchmark_task_jobs($task, $time_limit, $jobs);
                $this->recommend_time_limit($time_limit, $times);
            }
        }
    }
}

$eb = new EvalBenchmark();
$eb->run();
