<?php
require_once(IA_ROOT_DIR . "common/textblock.php");

// This module implements task and task-param related stuff.

// Get valid task types.
function task_get_types() {
    return array(
            'classic' => 'Clasic',
            'output-only' => 'Doar de output',
    );
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
                        'default' => 16384,
                        'type' => 'integer',
                        'name' => 'Limita de memorie',
                ),
                'tests' => array(
                        'description' => "Numar de teste",
                        'default' => 10,
                        'type' => 'integer',
                        'name' => "Numar de teste",
                ),
                'testgroups' => array(
                        'description' => "Descrierea gruparii testelor.",
                        'default' => '1;2;3;4;5;6;7;8;9;10',
                        'type' => 'string',
                        'name' => "Grupare teste",

            ),
            'okfiles' => array(
                    'description' => "Daca evaluator-ul foloseste fisiere .ok",
                    'default' => '0',
                    'type' => 'bool',
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
            'tests' => array(
                    'description' => "Numar de teste",
                    'default' => 10,
                    'type' => 'integer',
                    'name' => "Numar de teste",
            ),
            'testgroups' => array(
                    'description' => "Descrierea gruparii testelor.",
                    'default' => '1;2;3;4;5;6;7;8;9;10',
                    'type' => 'string',
                    'name' => "Grupare teste",
            ),
            'okfiles' => array(
                    'description' => "Daca evaluator-ul foloseste fisiere .ok",
                    'default' => '0',
                    'type' => 'bool',
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
            'title' => ucfirst($task_id),
            'hidden' => 1,
            'source' => 'ad-hoc',
            'page_name' => IA_TASK_TEXTBLOCK_PREFIX . $task_id,
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

    $open_source = getattr($task, 'open_source');
    if ($open_source != '0' && $open_source != '1') {
        $errors['open_source'] = 'Se accepta doar 0/1';
    }

    $open_tests = getattr($task, 'open_tests');
    if ($open_tests != '0' && $open_tests != '1') {
        $errors['open_tests'] = 'Se accepta doar 0/1';
    }

    if (!array_key_exists(getattr($task, 'type'), task_get_types())) {
        $errors['type'] = "Tipul task-ului este invalid";
    }

    if (!is_task_id(getattr($task, 'id', ''))) {
        $errors['id'] = 'ID de task invalid';
    }

    return $errors;
}

// Parse test grouping expression from task parameters and returns groups as an array
// If there is no grouping parameter defined it returns a group for each test by default
// If the expression string contains errors the function returns false
// Expression syntax:
// item: number | number-number 
// group: item | item,group
// groups: group | group;groups
function task_get_testgroups($parameters) {
    $test_count = $parameters['tests'];
    if (!is_whole_number($test_count)) {
        return false;
    }
    if (is_null(getattr($parameters, 'testgroups'))) {
        $testgroups = array();
        for ($test = 1; $test <= $test_count; $test++) {
            $group = array($test);
            $testgroups[] = $group;
        }
        return $testgroups;
    }

    $used_count = array();
    for ($test = 1; $test <= $test_count; $test++) {
        $used_count[$test]  = 0;
    }
    $testgroups = array();
    $groups = explode(';', $parameters['testgroups']);
    foreach ($groups as &$group) {
        $current_group = array();
        $items = explode(',', $group);
        foreach ($items as &$item) {
            $tests = explode('-', $item);
            if (count($tests) < 1 || count($tests) > 2) {
                return false;
            }
            foreach ($tests as &$test) {
                $test = trim($test);
                if (!is_whole_number($test)) {
                    return false;
                }
            }
            if (count($tests) == 1) {
                if ($tests[0] < 1 || $tests[0] > $test_count) {
                    return false;
                }
                $current_group[] = $tests[0];
                $used_count[$tests[0]]++;
            }
            else {
                $left = (int) $tests[0];
                $right = (int) $tests[1];
                if ($left < 1 || $right < 1 || $left > $test_count || $right > $test_count) {
                    return false;
                }
                for ($test = min($left, $right); $test <= max($left, $right); $test++) {
                    $current_group[] = $test;
                    $used_count[$test]++;
                }
            }
        }
        $testgroups[] = $current_group;
    }

    for ($test = 1; $test <= $test_count; $test++) {
        if ($used_count[$test] != 1) {
            return false;
        }
    }

    return $testgroups;
}

// Validate parameters. Return errors as $form_errors
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
            $errors['memlimit'] = "Minim 10 kilobytes.";
        } else if ($parameters['memlimit'] > 131072) {
            $errors['memlimit'] = "Maxim 128 megabytes.";
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

        if (task_get_testgroups($parameters) === false) {
            $errors['testgroups'] = "Eroare de sintaxa in expresie.";
        }

    } else if ($task_type == 'output-only') {
        if ($parameters['okfiles'] != '0' && $parameters['okfiles'] != '1') {
            $errors['okfiles'] = "0/1 only";
        }

        if (!is_whole_number($parameters['tests'])) {
            $errors['tests'] = "Numarul de teste trebuie sa fie un numar.";
        } else if ($parameters['tests'] < 1) {
            $errors['tests'] = "Minim 1 test.";
        } else if ($parameters['tests'] > 100) {
            $errors['tests'] = "Maxim 100 de teste.";
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

        if (task_get_testgroups($parameters) === false) {
            $errors['testgroups'] = "Eroare de sintaxa in expresie.";
        }
    } else {
        log_error("Bad task_type");
    }
    return $errors;
}

//FIXME: this is a hack; we should have a database table for this
function task_get_topic($task_id) {
    if (!is_task_id($task_id)) {
        log_error("Invalid task id");
    }

    // Get task
    $task = textblock_get_revision("problema/".$task_id);
    $pattern = '/==\ *smftopic\(\ *topic_id="\ *([0-9]*).*0*\ *"\ *\)\ *==/i';
    if (preg_match($pattern, $task['text'], $matches)) {
        return $matches[1];
    }
    return null;
}

?>
