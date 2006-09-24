#! /usr/bin/env php
<?php

require_once('config.php');
require_once('../common/log.php');
require_once('../common/db.php');
require_once('utilities.php');
require_once('ClassicGrader.php');

log_print("");
log_print("Judge started");
log_print("");

class JobResult {
    public $score;

    public $log;

    public $message;

    function __construct()
    {
        $log = "";
        $message = "";
        $score = 0;
    }
   
    // Returns a job result for a system error;
    static function SystemError()
    {
        $res = new JobResult();

        $res->log = "Eroare de sistem. Va rog sa trimiteti un mail la ".
            "brick@wall.com sau sa postati pe forum. Va rugam sa mentionati ".
            "id-ul jobului.";
        $res->message = "Eroare de sistem";
        $res->score = 0;

        return $res;
    }
}

function job_send_result($jobid, JobResult $result)
{
    if ($result->message == "Eroare de sistem") {
        log_warn("System error on job $jobid");
    } else {
        log_print("Sending job $jobid result (score {$result->score})");
    }
    job_mark_done($jobid, $result->log, $result->message, $result->score);
}

function get_job_result($job)
{
    if (!chdir(IA_EVAL_DIR)) {
        log_print("Can't chdir to eval dir");
        return JobResult::SystemError();
    }
    $task = task_get($job['task_id']);
    if (!$task) {
        log_print("Nu am putut lua task-ul " . $job['task_id']);
        return JobResult::SystemError();
    }
    $task_parameters = task_get_parameters($job['task_id']);
    if (!$task_parameters) {
        log_print("Nu am putut lua parametrii task-ului " . $job['task_id']);
        return JobResult::SystemError();
    }

    if ($task['type'] == 'classic') {
        $grader = new ClassicGrader($job['task_id'], $task_parameters);
        return $grader->Grade($job['file_contents'], $job['file_extension']);
    } else {
        log_print("Nu stiu sa evaluez task-uri de tip ".$task['type']);
        return JobResult::SystemError();
    }
}

// This function handles a certain job. Returns a JobResult
function handle_job($job) {
    log_print("- -- --- ---- ----- Handling job " . $job['id']);
    job_mark_processing($job['id']);

    $job_result = get_job_result($job);
    if ($job_result == null) {
        log_print("S-a belit get_job_result");
        $job_result = JobResult::SystemError();
    }

    job_send_result($job['id'], $job_result);
    log_print("- -- --- ---- ----- I'm done with this job");
    log_print("");
//    milisleep(5000);
}

// main function. C rules.
function main() {
    while (1) {
        while ($job = job_get_next_job()) {
            handle_job($job);
        }
        milisleep(10);
    }
}

main()

?>
