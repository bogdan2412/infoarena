<?php
require_once(IA_ROOT_DIR . "common/textblock.php");

// This module implements task and task-param related stuff.

// Get valid task types.
function task_get_types() {
    return array(
        'classic' => 'Clasică',
        'interactive' => 'Interactivă',
        // 'output-only' => 'Doar de output',
    );
}

/**
 * Get valid task security types
 * @return array
 */
function task_get_security_types() {
    return array(
        'private' => 'Private',
        'protected' => 'Protected',
        'public' => 'Public',
    );
}

// Get parameter infos.
function task_get_parameter_infos() {
    return array(
        'classic' => array(
            'timelimit' => array(
                'description' => 'Limita de timp (in secunde)',
                'default' => 1,
                'type' => 'float',
                'name' => 'Limita de timp',
            ),
            'memlimit' => array(
                'description' => 'Limita de memorie (in kilobytes)',
                'default' => 16384,
                'type' => 'integer',
                'name' => 'Limita de memorie',
            ),
        ),
        'interactive' => array(
            'timelimit' => array(
                'description' => 'Limita de timp (in secunde)',
                'default' => 1,
                'type' => 'float',
                'name' => 'Limita de timp',
            ),
            'memlimit' => array(
                'description' => 'Limita de memorie (in kilobytes)',
                'default' => 131072,
                'type' => 'integer',
                'name' => 'Limita de memorie',
            ),
            'interact' => array(
                'description' => 'Sursa programului interactiv.',
                'default' => 'interact.c',
                'type' => 'string',
                'name' => 'Programul interactiv',
            ),
        ),
        // 'output-only' => array(
        // ),
    );
}

// Initialize a task object
function task_init($task_id, $task_type, $user = null) {
    $task = array(
            'id' => $task_id,
            'type' => $task_type,
            'title' => ucfirst($task_id),
            'security' => 'private',
            'source' => 'ad-hoc',
            'page_name' => IA_TASK_TEXTBLOCK_PREFIX . $task_id,
            'open_source' => 0,
            'open_tests' => 0,
            'test_count' => 10,
            'test_groups' => NULL,
            'public_tests' => NULL,
            'evaluator' => '',
            'use_ok_files' => 1,
            'rating' => NULL,
    );

    // User stuff. ugly
    if (is_null($user)) {
        $task['user_id'] = 0;
    } else {
        $task['user_id'] = $user['id'];
    }

    log_assert_valid(task_validate($task));
    return $task;
}

// Validates a task.
// NOTE: this might be incomplete, so don't rely on it exclusively.
// Use this to check for a valid model. It's also useful in controllers.
function task_validate($task) {
    log_assert(is_array($task), "You didn't even pass an array");

    $errors = array();

    if (strlen(getattr($task, 'title', '')) < 1) {
        $errors['title'] = 'Titlu prea scurt.';
    }

    if (!is_page_name(getattr($task, 'page_name'))) {
        $errors['page_name'] = 'Homepage invalid';
    }

    if (!is_user_id(getattr($task, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid';
    }

    if (!array_key_exists(getattr($task, 'security'),
            task_get_security_types())) {
        $errors['security'] = 'Tipul securitatii este invalid';
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

    if (!is_whole_number($task['test_count'])) {
        $errors['test_count'] = "Numarul de teste trebuie sa fie un numar.";
    } else if ($task['test_count'] < 1) {
        $errors['test_count'] = "Minim 1 test.";
    } else if ($task['test_count'] > 100) {
        $errors['test_count'] = "Maxim 100 de teste.";
    }

    if ($task['use_ok_files'] != '0' && $task['use_ok_files'] != '1') {
        $errors['use_ok_files'] = "0/1 only";
    }

    if ($task['evaluator'] === '') {
        if (!$task['use_ok_files']) {
            $errors['evaluator'] =
                'Pentru evaluare cu diff e nevoie de fisiere .ok';
        }
    } else {
        if (!is_attachment_name($task['evaluator'])) {
             $errors['evaluator'] = 'Nume de fisier invalid pentru problema '
                                  . $task['id'];
        }
    }

    if (strlen(getattr($task, 'test_groups', '')) > 256) {
        $errors['test_groups'] = 'Expresia este prea lunga.';
    } else if (task_get_testgroups($task) === false) {
        $errors['test_groups'] = 'Eroare de sintaxa in expresie.';
    }

    if (task_parse_test_group($task["public_tests"],
                              $task["test_count"]) === false) {
        $errors['public_tests'] = 'Eroare de sintaxa in expresie.';
    }

    return $errors;
}

// Parse test grouping expression from task and returns groups as an array.
// If there is no grouping parameter defined it returns a group for each test
// by default.
// If the expression string contains errors the function returns false.
// Expression syntax:
// item: number | number-number
// group: item | item,group
// groups: group | group;groups
function task_parse_test_group($string, $test_count) {
    if (strlen($string) == 0) {
        return array();
    }

    $current_group = array();
    $items = explode(',', $string);
    $used_count = array();
    for ($test = 1; $test <= $test_count; $test++) {
        $used_count[$test]  = 0;
    }

    foreach ($items as &$item) {
        $tests = explode('-', $item);
        if (count($tests) < 1 || count($tests) > 2) {
            return false;
        }
        foreach ($tests as &$test_ref) {
            $test_ref = trim($test_ref);
            if (!is_whole_number($test_ref)) {
                return false;
            }
        }
        if (count($tests) == 1) {
            if ($tests[0] < 1 || $tests[0] > $test_count) {
                return false;
            }
            $current_group[] = $tests[0];
            $used_count[$tests[0]] = 1;
        } else {
            $left = (int) $tests[0];
            $right = (int) $tests[1];
            if ($left < 1 || $right < 1 ||
                $left > $test_count || $right > $test_count) {
                return false;
            }
            for ($test = min($left, $right); $test <= max($left, $right);
                 $test++) {
                $current_group[] = $test;
                $used_count[$test]++;
            }
        }
    }

    for ($test = 1; $test <= $test_count; $test++) {
        if ($used_count[$test] > 1) {
            return false;
        }
    }

    return $current_group;
}

function task_get_testgroups($task) {
    $test_count = $task['test_count'];
    if (!is_whole_number($test_count)) {
        return false;
    }
    if (!getattr($task, 'test_groups')) {
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
    $groups = explode(';', $task['test_groups']);
    foreach ($groups as &$group) {
        $current_group = task_parse_test_group($group, $test_count);
        if (!$current_group) {
            return false;
        }
        foreach ($current_group as $test_in_group) {
            $used_count[$test_in_group]++;
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
    if ($task_type === 'classic' || $task_type === 'interactive') {
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
        } else if ($parameters['memlimit'] > 524288) {
            $errors['memlimit'] = "Maxim 512 megabytes.";
        }
    }
    if ($task_type === 'interactive') {
        if ($parameters['interact'] === '') {
            $errors['interact'] = 'Trebuie specificat un program interactiv.';
        } else {
            if (!is_attachment_name($parameters['interact'])) {
                $errors['interact'] = 'Nume de fisier invalid.';
            }
        }
    }
    return $errors;
}

function task_get_topic($task_id) {
    if (!is_task_id($task_id)) {
        log_error("Invalid task id");
    }

    // Get task
    $task = task_get($task_id);
    $task_page = textblock_get_revision($task["page_name"]);
    return $task_page["forum_topic"];
}

// Receives a list of method and algorithm tag ids and links them to task_id
function task_update_tags($task_id, $method_tags_id, $algorithm_tags_id) {
    log_assert(is_array($method_tags_id), 'method_tags must be an array');
    log_assert(is_array($algorithm_tags_id), 'algorithm_tags must be an array');
    log_assert(is_task_id($task_id), "Invalid task_id");

    tag_clear('task', $task_id, 'method');
    tag_clear('task', $task_id, 'algorithm');

    foreach ($method_tags_id as $tag_id) {
        tag_add('task', $task_id, $tag_id);
    }

    foreach ($algorithm_tags_id as $tag_id) {
        tag_add('task', $task_id, $tag_id);
    }
}
