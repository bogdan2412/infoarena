<?php

require_once(IA_ROOT_DIR . "common/db/task.php");
require_once(IA_ROOT_DIR . "common/task.php");

// Displays form to either create a new task or edit an existing one.
// This form does not edit task content (its associated textblock)
// (textblock editor does that)
//
// Initially, the form is filled in with either:
//      * values for the existing task we edit
//      * default initial values when creating a new task
//
// Form submits to controller_task_save_details().
// When a validation error occurs in controller_task_save_details() it calls
// this controller as an error handler in order to display the form
// with the user-submitted data and their corresponding errors.
function controller_task_details($task_id) {
    // validate task_id
    if (!is_task_id($task_id)) {
        flash_error('Identificatorul de task este invalid');
        redirect(url_home());
    }

    // Get task
    $task = task_get($task_id);
    if (!$task) {
        flash_error("Problema nu exista");
        redirect(url_home());
    }

    // Security check
    identity_require('task-edit', $task);

    // get parameter list for tasks (in general, not for this specific task)
    $param_infos = task_get_parameter_infos();
    $task_params = task_get_parameters($task['id']);

    // Form stuff.
    $values = array();
    $errors = array();

    // Fill in form values from request, defaults in $task
    $values['author'] = request('author', $task['author']);
    $values['type'] = request('type', $task['type']);
    $values['source'] = request('source', $task['source']);
    $values['hidden'] = request('hidden', $task['hidden']);
    $values['title'] = request('title', $task['title']);
    $values['page_name'] = request('page_name', $task['page_name']);

    // Parameter values, for all possible types of tasks.
    // Yucky, but functional.
    foreach (task_get_types() as $task_type => $pretty_name) {
        foreach ($param_infos[$task_type] as $name => $info) {
            $form_name = "param_{$task_type}_{$name}";
            $def = $info['default'];
            if ($task_type == $task['type']) {
                $def = getattr($task_params, $name, $def);
            }
            $values[$form_name] = request($form_name, $def);
        }
    }

    // Validate the monkey.
    if (request_is_post()) {
        // Build new task
        $new_task = $task;
        $new_task['title'] = $values['title'];
        $new_task['page_name'] = $values['page_name'];
        $new_task['author'] = $values['author'];
        $new_task['source'] = $values['source'];
        $new_task['type'] = $values['type'];
        $new_task['hidden'] = $values['hidden'];

        $task_errors = task_validate($new_task);
        $errors = $task_errors;

        // Check security.
        if ($new_task['hidden'] != $task['hidden']) {
            identity_require('task-change-security', $task);
        }

        // Handle task parameters. Only for current type, and only if
        // properly selected.
        $new_task_params = $task_params;
        if (!array_key_exists('type', $task_errors)) {
            $task_type = $new_task['type'];
            foreach ($param_infos[$task_type] as $name => $info) {
                $form_name = "param_{$task_type}_{$name}";
                $new_task_params[$name] = $values[$form_name];
            }
            $task_params_errors = task_validate_parameters(
                    $task_type, $new_task_params);
            // Properly copy errors. Sucky
            foreach ($param_infos[$task_type] as $name => $info) {
                $form_name = "param_{$task_type}_{$name}";
                if (array_key_exists($name, $task_params_errors)) {
                    $errors[$form_name] = $task_params_errors[$name];
                }
            }
        }

        // If no errors then do the db monkey
        if (!$errors) {
            // FIXME: error handling? Is that even remotely possible in php?
            task_update_parameters($task_id, $new_task_params);
            task_update($new_task);

            flash("Task-ul a fost modificat cu succes.");
            redirect(url_task_edit($task_id));
        }
    }

    // Create view.
    $view = array();
    $view['title'] = "Editare $task_id";
    $view['page_name'] = url_task_edit($task_id);
    $view['task_id'] = $task_id;
    $view['task'] = $task;
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;
    $view['param_infos'] = $param_infos;

    execute_view_die("views/task_edit.php", $view);
}

// Creates a task. Minimalist
function controller_task_create()
{
    global $identity_user;

    // Security check. FIXME: sort of a hack.
    identity_require_login();
    identity_require("task-create",
            task_init('new_task', 'classic', $identity_user));

    // Form stuff.
    $values = array();
    $errors = array();

    // Get form values
    $values['id'] = request('id', '');
    $values['type'] = request('type', 'classic');

    if (request_is_post()) {
        if (!is_task_id($values['id'])) {
            $errors['id'] = "Id de task invalid. Nu se permit majuscule!";
        } else if (task_get($values['id'])) {
            $errors['id'] = "Exista deja un task cu acest id";
        }
        if (!array_key_exists($values['type'], task_get_types())) {
            $errors['type'] = "Tip de task invalid";
        }

        if (!$errors) {
            $task = task_init(
                    $values['id'],
                    $values['type'],
                    $identity_user);
            $task_params = array();
            // FIXME: array_ magic?
            $param_infos = task_get_parameter_infos();
            foreach ($param_infos[$values['type']] as $name => $info) {
                $task_params[$name] = $info['default'];
            }

            // This should never fail.
            log_assert(task_create($task, $task_params));
            flash("Un nou task a fost creeat, acum poti sa-l editezi");
            redirect(url_task_edit($task['id']));
        }
    }

    // Create view.
    $view = array();
    $view['title'] = "Creare task";
    $view['page_name'] = url_task_create();
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;

    execute_view_die("views/task_create.php", $view);
}

?>
