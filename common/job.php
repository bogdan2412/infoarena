<?php

require_once(IA_ROOT_DIR.'common/db/job.php');
require_once(IA_ROOT_DIR.'common/db/task.php');
require_once(IA_ROOT_DIR.'common/db/round.php');

// Safe function to validate and submit a job.
// $args contains:
//      task_id: Task to submit for.
//      round_id: Round to submit for. Optional, if missing the job is sent
//              to all parent rounds.
//      compiler_id: c, cpp or fpc.
//      solution: A string with the file to submit.
//
// Returns an array of errors, or array() on success.
function safe_job_submit($args, $user) {
    $errors = array();

    // Validate task id.
    if (!array_key_exists('task_id', $args)) {
        $errors['task_id'] = "Lipseste id-ul task-ului.";
    } else if (!is_task_id($args['task_id'])) {
        $errors['task_id'] = "Id de task invalid.";
    } else if (is_null($task = task_get($args['task_id']))) {
        $errors['task_id'] = "Task-ul {$args['task_id']} nu exista.";
    }

    // Validate round id.
    if (array_key_exists('round_id', $args)) {
        if (!is_round_id($args['round_id'])) {
            $errors['round_id'] = "Id de runda invalid";
        } else if (is_null($round = round_get($args['round_id']))) {
            $errors['round_id'] = "Runda '{$args['round_id']}' nu exista.";
        }
    } else {
        $round = null;
    }

    // Validate compiler id
    $valid_compilers = array('c', 'cpp', 'fpc');
    if (!array_key_exists('compiler_id', $args)) {
        $errors['compiler_id'] = "Lipseste compilatorul.";
    } else if (array_search($args['compiler_id'], $valid_compilers) === false) {
        $errors['compiler_id'] = "Compilator invalid.";
    }
    
    // Validate solution
    if (!array_key_exists('solution', $args)) {
        $errors['solution'] = "Lipseste fisierul solutie.";
    } else if (!is_string($args['solution'])) {
        $errors['solution'] = "Solution trebuie sa fie string.";
    } else if (IA_SUBMISSION_MAXSIZE <= strlen($args['solution'])) {
        $errors['solution'] = "Solutia depaseste dimensiunea maxima admisa:".
                ((int)IA_SUBMISSION_MAXSIZE / 1024).'KB.';
    }

    // Check task submit security
    if (!security_query($user, 'task-submit', $task)) {
        $errors[] = "Nu ai voie sa trimiti la acest task.";
    }
    if ($round != null && !security_query($round, 'task-submit', $round)) {
        $errors[] = "Nu poti sa trimiti la aceasta runda.";
    }

    // FIXME: check round-submit or something?

    // Only now create the job.
    if (count($errors) === 0) {
        // round_id can be missing.
        // If it's missing then the job is multiplied and sent to all parent rounds.
        // This is compatible with 2.1.3
        if (array_key_exists('round_id', $args)) {
            job_create($args['task_id'], $args['round_id'], $user['id'],
                    $args['compiler_id'], $args['solution']);
        } else {
            $parent_rounds = task_get_parent_rounds($args['task_id']);
            if (count($parent_rounds) === 0) {
                // some jobs just don't have a round
                job_create($args['task_id'], '', $user['id'],
                        $args['compiler_id'], $args['solution']);
            }
            else {
                foreach ($parent_rounds as $round_id) {
                    if (security_query($user, 'round-submit', round_get($round_id))) {
                        job_create($args['task_id'], $round_id, $user['id'],
                                $args['compiler_id'], $args['solution']);
                    }
                }
            }
        }
    }

    return $errors;
}
