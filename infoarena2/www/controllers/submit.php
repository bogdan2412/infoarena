<?php

// Big bad submit controller.
function controller_submit() {
    $action = request("action");
    if ('save' == $action) {
        $form_values = array(
            'round_id' => getattr($_POST, 'round_id'),
            'task_id' => getattr($_POST, 'task_id'),
            'compiler_id' => getattr($_POST, 'compiler_id')
        );
        $form_errors = array();

        // Check round
        $round = round_get($form_values['round_id']);
        if (!$round) {
            $errors['round_id'] = 'Va rugam sa alegeti runda la care doriti sa '
                                 . 'trimiteti solutie.';
        }

        // Check task
        $task = task_get($form_values['task_id']);
        if (!$task) {
            $errors['task_id'] = 'Va rugam sa alegeti problema la care doriti sa '
                                 . 'trimiteti solutie.';
        }

        // Check compiler.
        if ('output-only' != $task['type'] && false === 
            array_search($data['compiler_id'], array('c', 'cpp', 'fpc'))) {
            $errors['compiler_id'] = 'Compilator invalid.';
        }

        // Check uploaded solution
        if (isset($_FILES['solution'])) {
            if (is_uploaded_file($_FILES['solution']['tmp_name'])) {
                if (IA_SUBMISSION_MAXSIZE >= $_FILES['solution']['size']) {
                    $filepath = $_FILES['solution']['tmp_name'];
                    $file_buffer = file_get_contents($filepath);
                }
                else {
                    $form_errors['solution'] =
                        'Fisierul atasat depaseste dimensiunea maxima admisa!';
                }
            }
            else {
                $form_errors['solution'] = '
                    Fisierul atasat nu a putut fi citit! Incercati din nou.
                    Daca problema persista va rugam sa <a href="' .
                    url('Contact') . '">contactati administratorul</a>.';
            }
        }
        else {
            $form_errors['solution'] = 'Va rugam sa atasati fisierul solutie.';
        }

        // FIXME: permissions suck.
        identity_require('round-submit', $round);
        identity_require('task-submit', $task);

        // The end.
        if ($form_errors) {
            flash_error('NU am salvat solutia trimisa! Unul sau mai multe campuri
                         nu au fost completate corect.');
        }
        else {
            job_create($round['id'], $task['id'], getattr($identity_user, 'id'),
                       $form_values['compiler_id'], $file_buffer);
            // no errors => save submission
            flash('Solutia a fost salvata.');
        }
        // Fall through to submit form.
    } else {
        $form_errors = array();
        $form_values = array();
    }
    

    // get round list, filter by permissions.
    $rounds_unfiltered = round_get_info();
    $rounds = array();
    foreach ($rounds_unfiltered as $k => $r) {
        if (identity_can('round-submit', $r)) {
            $rounds[$k] = $r;
        }
    }

    // get task list.
    // FIXME: proper filter?
    $tasks_unfiltered = task_list_info();
    $tasks = array();
    foreach ($tasks_unfiltered as $k => $t) {
        if (identity_can('task-submit', $t)) {
            $tasks[$k] = $t;
        }
    }

    $view = array(
            'title' => 'Alegeti runda si task-ul pentru care vreti sa trimiteti solutii',
            'rounds' => $rounds,
            'tasks' => $tasks,
            'form_errors' => $form_errors,
            'form_values' => $form_values,
    );
    execute_view_die('views/submit.php', $view);
}

?>
