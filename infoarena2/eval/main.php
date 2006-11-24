#! /usr/bin/env php
<?php

require_once('config.php');
require_once(IA_ROOT.'common/log.php');
require_once(IA_ROOT.'common/common.php');
require_once(IA_ROOT.'common/task.php');
require_once(IA_ROOT.'common/security.php');
require_once(IA_ROOT.'common/db/db.php');
require_once('utilities.php');
require_once('download.php');
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

// Send job result.
function job_send_result($jobid, JobResult $result)
{
    if ($result->message == "Eroare de sistem") {
        log_warn("System error on job $jobid");
    } else {
        log_print("Sending job $jobid result (score {$result->score})");
    }
    job_mark_done($jobid, $result->log, $result->message, $result->score);

    log_print("");
    log_print("");
//    milisleep(5000);
}

// Evaluates job. Returns job result.
function job_eval($job)
{
    if (!chdir(IA_EVAL_DIR)) {
        log_print("Can't chdir to eval dir");
        return JobResult::SystemError();
    }
    
    // Get task
    $task = task_get($job['task_id']);
    if (!$task) {
        log_print("Nu am putut lua task-ul " . $job['task_id']);
        return JobResult::SystemError();
    }

    // Get task parameters.
    $task_parameters = task_get_parameters($job['task_id']);
    if (!$task_parameters) {
        log_print("Nu am putut lua parametrii task-ului " . $job['task_id']);
        return JobResult::SystemError();
    }

    // Make the grader and execute it.
    if ($task['type'] == 'classic') {
        $grader = new ClassicGrader($job['task_id'], $task_parameters);
        return $grader->Grade($job['file_contents'], $job['compiler_id']);
    } else {
        log_print("Nu stiu sa evaluez task-uri de tip ".$task['type']);
        return JobResult::SystemError();
    }
}

// This function handles a certain job.
// This is the main job function.
function job_handle($job) {
    log_print("- -- --- ---- ----- Handling job " . $job['id']);
    // FIXME: do this in query.
    job_mark_delay($job['id'], 'waiting');

    $user = user_get_by_id($job['user_id']);
    if (!$user) {
        log_print("Nu am gasit utilizatorul " . $job['user_id']);
        job_send_result($job, JobResult::SystemError());
        return;
    }

    // Evaluating, mark as processing.
    job_mark_delay($job['id'], 'processing');
    $job_result = job_eval($job);
    if ($job_result == null) {
        log_print("Bug in get_job_result");
        $job_result = JobResult::SystemError();
    }

    job_send_result($job['id'], $job_result);
}

// main function. C rules.
function main() {
    while (1) {
        while ($job = job_get_next_job()) {
            job_handle($job);
        }
        milisleep(10);
    }
}

main()

?>
