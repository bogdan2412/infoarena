#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR."common/db/task.php");
require_once(IA_ROOT_DIR."common/db/job.php");

ini_set("memory_limit", "128M");

db_connect();

$query = "DROP TABLE IF EXISTS `ia_job_test`";
db_query($query);
$query = "CREATE TABLE `ia_job_test` (
  `job_id` int(11) NOT NULL,
  `test_number` tinyint(4) NOT NULL,
  `test_group` tinyint(4) NOT NULL,
  `exec_time` int(11) default NULL,
  `mem_used` int(11) default NULL,
  `grader_exec_time` int(11) default NULL,
  `grader_mem_used` int(11) default NULL,
  `points` int(11) default NULL,
  `grader_message` varchar(128) default NULL,
  PRIMARY KEY  (`job_id`,`test_number`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

db_query($query);

$filter = array();
$job_count = job_get_count($filter);
$jobs = job_get_range($filter, 0, $job_count);

foreach ($jobs as $job) {
    log_print("Procesez job-ul ".$job['id']." -> ".$job['task_id']."...");
    $job_id = $job['id'];
    $eval_log = $job['eval_log'];
    $task_id = $job['task_id'];
    if (!is_task_id($task_id)) {
        continue;
    }
    $task_params = task_get_parameters($task_id);
    $test_count = $task_params['tests'];
    $test_groups = task_get_testgroups($task_params);
    $test_group = array();
    $idx =  1;
    foreach ($test_groups as $group) {
        foreach ($group as $test) {
            $test_group[$test] = $idx;
        }
        $idx++;
    }

    $new_eval_log = "";
    $lines = explode("\n", $eval_log); 
    foreach ($lines as $line) {
        if (preg_match('/^Punctaj total: ([0-9]+)$/', $line, $matches)) {
            continue;
        }
        if (preg_match('/^Rulez testul ([0-9]+): '.
                '([^:]*): timp ([0-9]+)ms: mem ([0-9]+)kb: '.
                '(.*): ([0-9]+) puncte$/', $line, $matches)) {
            $test_no = $matches[1];
            $test_status = $matches[2];
            $test_time = $matches[3];
            $test_mem = $matches[4];
            $test_msg = $matches[5];
            $test_score = $matches[6];
            job_test_update($job_id, $test_no, $test_group[$test_no], $test_time, $test_mem, 
                            null, null, $test_score, $test_msg);
            continue;
        }
        else {
            $new_eval_log .= $line."\n";
        }
    }

    job_update($job_id, $job['status'], $job['eval_message'], $new_eval_log, $job['score']);
}

$query = "OPTIMIZE TABLE `ia_job`";
db_query($query);

?>
