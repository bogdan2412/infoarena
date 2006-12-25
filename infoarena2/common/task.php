<?php
// This module implements task and task-param related stuff.

// Get valid task types.
function task_get_types() {
    return array('classic', 'output-only');
}

// Get parameter infos.
function task_get_parameter_infos() {
    return array(
            'classic' => array(
                    'timelimit' => array(
                            'description' => "Limita de timp (in secunde)",
                            'default' => 1,
                            'type' => 'float',
                            'name' => 'Limita de timp',
                    ),
                    'memlimit' => array(
                            'description' => "Limita de memorie (in kilobytes)",
                            'default' => 16000,
                            'type' => 'integer',
                            'name' => 'Limita de memorie',
                    ),
                    'tests' => array(
                            'description' => "Numar de teste",
                            'default' => 10,
                            'type' => 'integer',
                            'name' => "Numar de teste",
                    ),
                    'okfiles' => array(
                            'description' => "Daca evaluator-ul foloseste fisiere .ok",
                            'default' => '0',
                            'type' => 'boolean',
                            'name' => "Foloseste .ok",
                    ),
                    'evaluator' => array(
                            'description' => "Sursa evaluatorului. Poate fi omis pentru evaluare cu diff",
                            'default' => 'eval.c',
                            'type' => 'string',
                            'name' => "Evaluator",
                    ),
            ),
            'output-only' => array(
                    'okfiles' => array(
                            'description' => "Daca evaluator-ul foloseste fisiere .ok",
                            'default' => '0',
                            'type' => 'boolean',
                            'name' => "Foloseste .ok",
                    ),
                    'evaluator' => array(
                            'description' => "Sursa evaluatorului. Poate fi omis pentru evaluare cu diff",
                            'default' => 'eval.c',
                            'type' => 'string',
                            'name' => "Evaluator",
                    ),
            ),
    );
}

// Initialize a task object
function task_init($task_id, $task_type, $user = null) {
    $task = array(
            'id' => $task_id,
            'type' => $task_type,
            'title' => $task_id,
            'hidden' => 1,
            'source' => 'ad-hoc',
            'page_name' => TB_TASK_PREFIX . $task_id,
    );

    // User stuff. ugly
    if (is_null($user)) {
        $task['author'] = 'Unknown';
        $task['user_id'] = 0;
    } else {
        $task['author'] = $user['full_name'];
        $task['user_id'] = $user['id'];
    }

    log_assert_valid(task_validate($task));
    return $task;
}

// Validates a task.
// NOTE: this might be incomplete, so don't rely on it exclusively.
// Use this to check for a valid model. It's also usefull in controllers.
function task_validate($task) {
    $errors = array();

    // FIXME How to handle this?
    log_assert(is_array($task), "You didn't even pass an array");

    if (strlen(getattr($task, 'title', '')) < 1) {
        $errors['title'] = 'Titlu prea scurt.';
    }

    if (!is_page_name(getattr($task, 'page_name'))) {
        $errors['page_name'] = 'Homepage invalid';
    }

    if (!is_user_id(getattr($task, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid';
    }

    $hidden = getattr($task, 'hidden');
    if ($hidden != '0' && $hidden != '1') {
        $errors['hidden'] = 'Se accepta doar 0/1';
    }

    if (!in_array(getattr($task, 'type', ''), task_get_types())) {
        $errors['type'] = "Tipul task-ului este invalid";
    }

    if (!is_task_id(getattr($task, 'id', ''))) {
        $errors['id'] = 'ID de task invalid';
    }

    return $errors;
}

// Valideaza parametrii. Returneaza errorile sub conventie de $form_errors.
function task_validate_parameters($task_type, $parameters) {
    $errors = array();
    if ($task_type == 'classic') {
        if (!is_numeric($parameters['timelimit'])) {
            $errors['timelimit'] = "Limita de timp trebuie sa fie un numar.";
        } else if ($parameters['timelimit'] < 0.01) {
            $errors['timelimit'] = "Minim 10 milisecunde.";
        } else if ($parameters['timelimit'] > 60) {
            $errors['timelimit'] = "Maxim un minut.";
        }

        if (!is_whole_number($parameters['memlimit'])) {
            $errors['memlimit'] = "Limita de memorie trebuie sa fie un numar.";
        } else if ($parameters['memlimit'] < 10) {
            $errors['memlimit'] = "Minim 10 kilo.";
        } else if ($parameters['memlimit'] > 128000) {
            $errors['memlimit'] = "Maxim 128 mega.";
        }

        if (!is_whole_number($parameters['tests'])) {
            $errors['tests'] = "Numarul de teste trebuie sa fie un numar.";
        } else if ($parameters['tests'] < 1) {
            $errors['tests'] = "Minim 1 test.";
        } else if ($parameters['tests'] > 100) {
            $errors['tests'] = "Maxim 100 de teste.";
        }

        if ($parameters['okfiles'] != '0' && $parameters['okfiles'] != '1') {
            $errors['okfiles'] = "0/1 only";
        }

        if ($parameters['evaluator'] == "") {
            if (!$parameters['okfiles']) {
                $errors['evaluator'] = "Pentru evaluare cu diff e nevoie e fisiere .ok";
            }
        } else {
            if (!is_attachment_name($parameters['evaluator'])) {
                $errors['evaluator'] = "Nume de fisier invalid.";
            }
        }
    } else if ($task_type == 'output-only') {
        if ($parameters['okfiles'] != '0' && $parameters['okfiles'] != '1') {
            $errors['okfiles'] = "0/1 only";
        }

        if ($parameters['evaluator'] == "") {
            if (!$parameters['okfiles']) {
                $errors['evaluator'] = "Pentru evaluare cu diff e nevoie e fisiere .ok";
            }
        } else {
            if (!is_attachment_name($parameters['evaluator'])) {
                $errors['evaluator'] = "Nume de fisier invalid.";
            }
        }
    } else {
        log_error("Bad task_type");
    }
    return $errors;
}

?>
