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

// Only benchmark these users' sources.
const ADMINS = [ 'francu', 'Catalin.Francu', 'mihai.tutu' ];

db_connect();

// Load user IDs.
$adminIds = [];
foreach (ADMINS as $username) {
    $user = user_get_by_username($username);
    $user or die("Fatal: Admin \"{$username}\" not found.\n");
    $adminIds[] = $user['id'];
}

// Load all tasks.
$tasks = task_get_all();
foreach ($tasks as $i => $task) {
    // Load jobs submitted by admins and having a score of 100.
    $jobs = job_get_by_task_id_user_ids_status_score(
        $task['id'], $adminIds, 'done', 100);
    printf("== Task %d/%d (%s, %d tests): ",
           $i + 1, count($tasks), $task['id'], $task['test_count']);

    $task_params = task_get_parameters($task['id']);
    isset($task_params['timelimit']) or die("HAHA\n");

    if (empty($jobs)) {
        printf("SKIPPING (no admin jobs)\n");
    } else if (!isset($task_params['timelimit'])) {
        printf("SKIPPING (time limit not set)\n");
    } else {
        printf("Benchmarking %d jobs\n", count($jobs));
        foreach ($jobs as $j => $job) {
            printf("  Benchmarking job #%d\n", $job['id']);
        }
    }
}
