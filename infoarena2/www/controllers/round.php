<?php

// View a contest round
function controller_round_view($round_id) {
    // If the round is missing jump to the edit/create controller.
    $round = round_get($round_id);
    if ($round) {
        identity_require('round-view', $round);
    }
    else {
        controller_round_edit($round_id);
    }

    // get textblock
    $textblock = round_get_textblock($round_id);

    // call view
    $view = array();
    $view['title'] = $textblock['title'];
    $view['page_name'] = 'round/' . $round;
    $view['textblock'] = $textblock;
    $view['round'] = $round;
    execute_view_die('views/wikiview.php', $view);
}

// Displays a form to either create a new round or edit an existing one.
//
// Initially, the form is filled in with either:
//      * values for the existing round we edit
//      * default initial values when creating a new round
//
// Form submits to controller_round_save().
// When a validation error occurs in controller_round_save() it calls
// this controller (controller_round_edit) as an error handler in order
// to display the form with the user-submitted data and their corresponding
// errors.
function controller_round_edit($round_id, $form_data = null,
                               $form_errors = null) {
    global $identity_user;
    $round = round_get($round_id);
    if ($round) {
        identity_require('round-edit', $round);
    }
    else {
        identity_require('round-create');
    }

    // get parameter list for rounds (in general, not for this specific round)
    $param_list = parameter_list('round');
    // here we store parameter values
    $param_values = array();

    if (is_null($form_data)) {
        // initial form data
        $form_data = array();
        $form_errors = array();
        if (!$round) {
            // default values (when creating a new round)
            $form_data['title'] = $round_id;
            $template = textblock_get_revision('template/new_round');
            $form_data['text'] = $template['text'];
            $form_data['type'] = '';

            // default parameter values
            foreach ($param_list as $k => $v) {
                $param_values[$k] = $v['default'];
                $form_data['p_' . $k] = $v['default'];
            }
        }
        else {
            // values from existing round
            $textblock = round_get_textblock($round_id);

            $form_data['title'] = $textblock['title'];
            $form_data['text'] = $textblock['text'];
            $form_data['type'] = $round['type'];

            // get round parameter values
            $param_values = round_get_parameters($round_id);
            foreach ($param_values as $k => $v) {
                $form_data['p_' . $k] = $v;
            }

            // get attached task list
            $tasks = round_get_task_info($round_id);
            $form_data['tasks'] = array_keys($tasks);
        }
    }
    else {
        // extract parameter values from form_data.
        // the `save` controller does a nice thing and extracts these values
        // for convenience
        $param_values = $form_data['_param_values'];
    }

    // obtain task list
    $all_tasks = task_list_info();

    // view form
    $view = array();
    //  - choose title
    if (!$round) {
        $view['title'] = "Runda noua: " . $round_id;
    }
    else {
        $view['title'] = "Modificare runda";
    }
    //  - choose active tab
    if (getattr($form_errors, '_param_list')) {
        $view['active_tab'] = 'parameters';
    }
    if (getattr($form_errors, 'tasks')) {
        $view['active_tab'] = 'tasks';
    }
    if (getattr($form_errors, '_param_list')) {
        $view['active_tab'] = 'parameters';
    }
    //  - feed other values
    $view['action'] = url('round/' . $round_id, array('action' => 'save'));
    $view['form_values'] = $form_data;
    $view['form_errors'] = $form_errors;
    $view['param_list'] = $param_list;
    $view['param_values'] = $param_values;
    $view['all_tasks'] = $all_tasks;
    execute_view_die("views/round_edit.php", $view);
}

// save controller
// Workflow is:
//      * controller_round_edit() displays form
//      * form submits to controller_round_save()
//      * controller_round_save() validates and uses controller_round_edit()
//        as error handler
function controller_round_save($round_id) {
    global $identity_user;
    $round = round_get($round_id);
    if ($round) {
        identity_require('round-edit', $round);
    }
    else {
        identity_require('round-create');
    }

    // get parameter list for rounds (in general, not for this specific round)
    $param_list = parameter_list('round');

    // Validate data. Put incoming data in `data` and errors in `errors`
    $data = array();
    $errors = array();
    $data['title'] = getattr($_POST, 'title');
    $data['text'] = getattr($_POST, 'text');
    $data['type'] = getattr($_POST, 'type');
    // get parameter values (all incoming POST variables that start with 'p_')
    $param_values = array();
    foreach ($_POST as $k => $v) {
        if ('p_' != substr($k, 0, 2)) continue;
        $id = substr($k, 2);
        if (!isset($param_list[$id])) continue;
        $param_values[substr($k, 2)] = $v;
        $data[$k] = $v;
    }
    $data['_param_values'] = $param_values;
    // validate round values
    if (strlen($data['text']) < 1) {
        $errors['text'] = "Va rugam sa completati enuntul.";
    }
    if (strlen($data['title']) < 1) {
        $errors['title'] = "Va rugam sa completati titlul.";
    }
    if (strlen($data['type']) < 1) {
        $errors['type'] = "Alegeti tipul rundei.";
        $errors['_param_list'] = true;
    }
    // validate parameter values
    foreach ($param_values as $k => $v) {
        if (!parameter_validate($param_list[$k], $v)) {
            $errors['p_' . $k] = 'Valoare invalida';
            $errors['_param_list'] = true;
        }
    }

    // validate attached task list
    $all_tasks = task_list_info();
    $tasks = getattr($_POST, 'tasks');
    $data['tasks'] = $tasks;
    foreach ($tasks as $task_id) {
        if (!isset($all_tasks[$task_id])) {
            $errors['tasks'] = 'Valori invalide! Hacking?';
            break;
        }
    }

    // process data
    if (!$errors) {
        // no errors => do stuff

        // - create/update round
        if ($round) {
            round_update($round_id, $data['type']);
            // note: updating a round does not change its owner (user_id)
        }
        else {
            round_create($round_id, $data['type'],
                         getattr($identity_user, 'id'));
        }
        // - corresponding textblock
        textblock_add_revision('round/' . $round_id, $data['title'],
                               $data['text'], getattr($identity_user,'id'));
        // - update parameter values
        round_update_parameters($round_id, $param_values);
        // - update attached task list
        round_update_task_list($round_id, $tasks);
        // - done
        flash('Am salvat modificarile');
        redirect(url('round/' . $round_id));
    }
    else {
        // errors occured => call on error handler
        flash_error('Unul sau mai multe campuri au fost completate incorect!');
        controller_round_edit($round_id, $data, $errors);
    }
}
?>
