<?php

// View a task
function controller_task_view($task_id) {
    // If the task is missing jump to the edit/create controller.
    $task = task_get($task_id);
    if ($task) {
        identity_require('task-view', $task);
    }
    else {
        controller_task_edit($task_id);
    }

    // get textblock
    $textblock = task_get_textblock($task_id);

    // call view
    $view = array();
    $view['title'] = $textblock['title'];
    $view['page_name'] = 'task/' . $task;
    $view['textblock'] = $textblock;
    $view['task'] = $task;
    execute_view_die('views/task_view.php', $view);
}

// Displays a form to either create a new task or edit an existing one.
//
// Initially, the form is filled in with either:
//      * values for the existing task we edit
//      * default initial values when creating a new task
//
// Form submits to controller_task_save().
// When a validation error occurs in controller_task_save() it calls
// this controller (controller_task_edit) as an error handler in order
// to display the form with the user-submitted data and their corresponding
// errors.
function controller_task_edit($task_id, $form_data = null,
                              $form_errors = null) {
    global $identity_user;
    $task = task_get($task_id);
    if ($task) {
        identity_require('task-edit', $task);
    }
    else {
        identity_require('task-create');
    }

    // validate task_id
    if (!preg_match('/^[a-z0-9][a-z0-9_]*$/i', $task_id)) {
        flash_error('id-ul de task este invalid');
        redirect(url(''));
    }

    // get parameter list for tasks (in general, not for this specific task)
    $param_list = parameter_list('task');
    // here we store parameter values
    $param_values = array();

    if (is_null($form_data)) {
        // initial form data
        $form_data = array();
        $form_errors = array();
        if (!$task) {
            // default values (when creating a new task)
            $form_data['title'] = $task_id;
            $form_data['author'] = getattr($identity_user, 'full_name');
            $template = textblock_get_revision('template/new_task');
            $form_data['text'] = $template['text'];
            $form_data['source'] = '';
            $form_data['type'] = '';

            // default parameter values
            foreach ($param_list as $k => $v) {
                $param_values[$k] = $v['default'];
                $form_data['p_' . $k] = $v['default'];
            }
        }
        else {
            // values from existing task
            $textblock = task_get_textblock($task_id);

            $form_data['title'] = $textblock['title'];
            $form_data['author'] = $task['author'];
            $form_data['text'] = $textblock['text'];
            $form_data['source'] = $task['source'];
            $form_data['type'] = $task['type'];

            // get task parameter values
            $param_values = task_get_parameters($task_id);
            foreach ($param_values as $k => $v) {
                $form_data['p_' . $k] = $v;
            }
        }
    }
    else {
        // extract parameter values from form_data
        $param_values = $form_data['_param_values'];
    }

    // view form
    $view = array();
    if (!$task) {
        $view['title'] = "Task nou: " . $task_id;
    }
    else {
        $view['title'] = "Modificare task";
    }
    $view['action'] = url('task/' . $task_id, array('action' => 'save'));
    $view['form_values'] = $form_data;
    $view['form_errors'] = $form_errors;
    $view['param_list'] = $param_list;
    $view['param_values'] = $param_values;
    execute_view_die("views/task_edit.php", $view);
}

// save controller
// Workflow is:
//      * controller_task_edit() displays form
//      * form submits to controller_task_save()
//      * controller_task_save() validates and uses controller_task_edit()
//        as error handler
function controller_task_save($task_id) {
    global $identity_user;
    $task = task_get($task_id);
    if ($task) {
        identity_require('task-edit', $task);
    }
    else {
        identity_require('task-create');
    }

    // validate task_id
    if (!preg_match('/^[a-z0-9][a-z0-9_]*$/i', $task_id)) {
        flash_error('id-ul de task este invalid');
        redirect(url(''));
    }

    // get parameter list for tasks (in general, not for this specific task)
    $param_list = parameter_list('task');

    // Validate data. Put incoming data in `data` and errors in `errors`
    $data = array();
    $errors = array();
    $data['title'] = getattr($_POST, 'title');
    $data['author'] = getattr($_POST, 'author');
    $data['text'] = getattr($_POST, 'text');
    $data['source'] = getattr($_POST, 'source');
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
    // validate task values
    if (strlen($data['text']) < 1) {
        $errors['text'] = "Va rugam sa completati enuntul.";
    }
    if (strlen($data['title']) < 1) {
        $errors['title'] = "Va rugam sa completati titlul.";
    }
    if (strlen($data['author']) < 1) {
        $errors['author'] = "Va rugam sa completati autorul/autorii.";
    }
    if (strlen($data['type']) < 1) {
        $errors['type'] = "Alegeti tipul task-ului.";
    }
    // validate parameter values
    foreach ($param_values as $k => $v) {
        if (!parameter_validate($param_list[$k], $v)) {
            $errors['p_' . $k] = 'Valoare invalida';
            $errors['_param_list'] = true;
        }
    }

    // process data
    if (!$errors) {
        // no errors => do stuff
        
        // - create/update task
        if ($task) {
            task_update($task_id, $data['type'], $data['author'],
                        $data['source']);
            // note: updating a task does not change its owner (user_id)
        }
        else {
            task_create($task_id, $data['type'], $data['author'],
                        $data['source'], getattr($identity_user, 'id'));
        }
        // - corresponding textblock
        textblock_add_revision('task/' . $task_id, $data['title'],
                               $data['text'], getattr($identity_user,'id'));
        // - update parameter values
        task_update_parameters($task_id, $param_values);
        // - done
        flash('Task-ul a fost salvat');
        redirect(url('task/' . $task_id));
    }
    else {
        // errors occured => call on error handler
        flash_error('Unul sau mai multe campuri au fost completate incorect!');
        controller_task_edit($task_id, $data, $errors);
    }
}
?>
