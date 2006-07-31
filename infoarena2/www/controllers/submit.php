<?php

// Displays form to submit task solution. User can choose from all
// tasks associated to a given round.
//
// If no round is specified, it displays a form from which to choose one
function controller_submit_form($round_id, $form_data = null,
                                $form_errors = null) {
    if (!$round_id) {
        // no round specified: present user a form from which to select
        // round

        // get round list
        $rounds_unfiltered = round_get_info();

        // filter rounds by user permissions
        $rounds = array();
        foreach ($rounds_unfiltered as $k => $r) {
            if (!identity_can('round-submit', $r)) {
                continue;
            }
            $rounds[$k] = $r;
        }

        // view
        $view = array(
            'title' => 'Alegeti runda pentru care trimiteti solutii',
            'rounds' => $rounds
        );
        execute_view_die('views/submit_choose.php', $view);
    }

    // validate round id
    $round = round_get($round_id);
    if (!$round) {
        flash_error('Nu exista aceasta runda.');
        redirect(url('submit'));
    }

    // round permissions
    identity_require('round-submit', $round);

    // get task list
    $tasks_unfiltered = round_get_task_info($round_id);

    // filter tasks by user permissions
    $tasks = array();
    foreach ($tasks_unfiltered as $k => $t) {
        if (!identity_can('task-submit', $t)) {
            continue;
        }
        $tasks[$k] = $t;
    }

    // view
    $textblock = round_get_textblock($round_id);
    $view = array(
        "tasks" => $tasks,
        "title" => 'Trimite solutii pentru ' . $textblock['title'],
        'round_id' => $round_id,
        'form_values' => $form_data,
        'form_errors' => $form_errors
    );
    execute_view_die('views/submit_form.php', $view);
}

function controller_submit_save($round_id) {
    global $identity_user;

    // validate round id
    $round = round_get($round_id);

    if (!$round) {
        // user did not specify round or round id is invalid =>
        // present user a form to choose preferred round
        controller_submit_form($round_id);
    }

    // permissions
    identity_require('round-submit', $round);

    // incoming data
    $data = array(
        'task_id' => getattr($_POST, 'task_id'),
        'compiler_id' => getattr($_POST, 'compiler_id')
    );

    // validate incoming data
    $errors = array();
    $task = task_get($data['task_id']);
    if (!$task) {
        $errors['task_id'] = 'Va rugam sa alegeti problema la care doriti sa '
                             . 'trimiteti solutie.';
    }
    if ('output-only' != $task['type'] && false === 
        array_search($data['compiler_id'], array('c', 'cpp', 'fpc'))) {
        $errors['compiler_id'] = 'Compilator invalid.';
    }

    // validate file
    if (isset($_FILES['solution'])) {
        if (is_uploaded_file($_FILES['solution']['tmp_name'])) {
            if (IA_SUBMISSION_MAXSIZE >= $_FILES['solution']['size']) {
                $filepath = $_FILES['solution']['tmp_name'];
                $file_buffer = file_get_contents($filepath);
            }
            else {
                $errors['solution'] =
                    'Fisierul atasat depaseste dimensiunea maxima admisa!';
            }
        }
        else {
            $errors['solution'] = '
                Fisierul atasat nu a putut fi citit! Incercati din nou.
                Daca problema persista va rugam sa <a href="' .
                url('Contact') . '">contactati administratorul</a>.';
        }
    }
    else {
        $errors['solution'] = 'Va rugam sa atasati fisierul solutie.';
    }

    // process
    if ($errors) {
        flash_error('NU am salvat solutia trimisa! Unul sau mai multe campuri
                     nu au fost completate corect.');
        controller_submit_form($round_id, $data, $errors);
    }
    else {
        job_create($round_id, $task['id'], getattr($identity_user, 'id'),
                   $data['compiler_id'], $file_buffer);
        // no errors => save submission
        flash('Solutia a fost salvata.');
        redirect(url('submit/' . $round_id));
    }
}

?>
