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
                            'default' => false,
                            'type' => 'boolean',
                            'name' => "Foloseste .ok",
                    ),
                    'unique_output' => array(
                            'description' => "Evaluare cu diff.",
                            'default' => false,
                            'type' => 'boolean',
                            'name' => "Output unic",
                    ),
                    'evaluator' => array(
                            'description' => "Numele fisierului atasat, fara grader_.",
                            'default' => 'eval.c',
                            'type' => 'string',
                            'name' => "Evaluator",
                    ),
            ),
            'output-only' => array(
                    'okfiles' => array(
                            'description' => "Daca evaluator-ul foloseste fisiere .ok",
                            'default' => false,
                            'type' => 'boolean',
                            'name' => "Foloseste .ok",
                    ),
                    'unique_output' => array(
                            'description' => "Evaluare cu diff.",
                            'default' => false,
                            'type' => 'boolean',
                            'name' => "Output unic",
                    ),
                    'evaluator' => array(
                            'description' => "Numele fisierului atasat, fara grader_.",
                            'default' => 'eval.c',
                            'type' => 'string',
                            'name' => "Evaluator",
                    ),
            ),
    );
}

function task_get_parameter_infos_hack() {
    $infos = task_get_parameter_infos();
    $ret = array_merge($infos['classic'], $infos['output-only']);
    return $ret;
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
        if ($parameters['unique_output'] != '0' && $parameters['unique_output'] != '1') {
            $errors['unique_output'] = "0/1 only";
        }

        if (!preg_match("/[a-bA-B0-9\-\.]+/", $parameters['evaluator'])) {
            $errors['evaluator'] = "Nume de fisier invalid.";
        }
    } else if ($task_type == 'output-only') {
        if ($parameters['okfiles'] != '0' && $parameters['okfiles'] != '1') {
            $errors['okfiles'] = "0/1 only";
        }
        if ($parameters['unique_output'] != '0' && $parameters['unique_output'] != '1') {
            $errors['unique_output'] = "0/1 only";
        }
        if (!preg_match("/[a-bA-B0-9\-\.]+/", $parameters['evaluator'])) {
            $errors['evaluator'] = "Nume de fisier invalid.";
        }
    } else {
        log_error("Bad task_type");
    }
    return $errors;
}

?>
