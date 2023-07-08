<?php

require_once(Config::ROOT.'common/db/job.php');
require_once(Config::ROOT.'common/db/task.php');
require_once(Config::ROOT.'common/db/round.php');

// Safe function to validate and submit a job.
// $args contains:
//      task_id: Task to submit for.
//      round_id: Round to submit for. Optional, if missing the job is sent
//              to all parent rounds.
//      compiler_id: c-32, cpp-32, c-64, cpp-64, fpc, java, py or rs
//      solution: A string with the file to submit.
//
// Returns an array of errors, or array() on success.
function safe_job_submit($args, $user) {
    $errors = array();

    // Validate task id.
    $task = null;
    if (!array_key_exists('task_id', $args)) {
        $errors['task_id'] = "Lipsește id-ul task-ului.";
    } else if (!is_task_id($args['task_id'])) {
        $errors['task_id'] = "Id de task invalid.";
    } else if (is_null($task = task_get($args['task_id']))) {
        $errors['task_id'] = "Task-ul {$args['task_id']} nu există.";
    }
    $taskId = $task['id'] ?? '';

    // Validate round id.
    $round = null;
    if (!array_key_exists('round_id', $args)) {
        $errors['round_id'] = "Nu ai specificat un concurs.";
    } else if (!is_round_id($args['round_id'])) {
        $errors['round_id'] = "Nu ai specificat un concurs corect.";
    } else if (is_null($round = round_get($args['round_id']))) {
        $errors['round_id'] = "Runda '{$args['round_id']}' nu există.";
    }
    // Check if task is new and hasn't been added to any rounds
    if (getattr($args, "round_id") == "" &&
        !task_get_submit_rounds($taskId, $user) &&
        security_query($user, 'task-submit', $task)) {
        unset($errors["round_id"]);
    }

    // Validate compiler id
    $valid_compilers = array(
        'c-32',
        'cpp-32',
        'c-64',
        'cpp-64',
        'fpc',
        'py',
        'java',
        'rs',
    );

    if (!array_key_exists('compiler_id', $args)) {
        $errors['compiler_id'] = "Lipsește compilatorul.";
    } else if (array_search($args['compiler_id'], $valid_compilers) === false) {
        $errors['compiler_id'] = "Compilator invalid.";
    }

    // Validate solution
    if (!array_key_exists('solution', $args)) {
        $errors['solution'] = "Lipsește fișierul soluție.";
    } else if (!is_string($args['solution'])) {
        $errors['solution'] = "Solution trebuie să fie string.";
    } else if (IA_SUBMISSION_MAXSIZE <= strlen($args['solution'])) {
        $errors['solution'] = "Soluția depășește dimensiunea maximă admisă: ".
                ((int)IA_SUBMISSION_MAXSIZE / 1024).' KB.';
    }

    // Check task submit security
    if ($task && !security_query($user, 'task-submit', $task)) {
        $errors[] = "Nu ai voie să trimiți la acest task.";
    }
    if ($round && !security_query($user, 'round-submit', $round)) {
        $errors[] = "Nu poți să trimiți la această rundă.";
    }

    // Check if the user has submitted too many times

    if (job_archive_waiting_number($user) >= IA_USER_MAX_ARCHIVE_WAITING_JOBS) {
        $errors['submit_limit'] = "Nu ai dreptul să ai mai mult de " .
                    IA_USER_MAX_ARCHIVE_WAITING_JOBS .
                    " submisii în așteptare la un moment dat.";
    }

    // Only now create the job.
    if (count($errors) === 0) {
        job_create($args['task_id'], $args['round_id'], $user['id'],
                $args['compiler_id'], $args['solution'],
                getattr($args, 'remote_ip_info'));
    }

    return $errors;
}
