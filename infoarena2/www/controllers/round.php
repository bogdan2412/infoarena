<?php

require_once(IA_ROOT . "common/round.php");

/* FIXME: disabled
// Displays a form to either create a new round or edit an existing one.
// This form does not edit round content (its associated textblock)
// (textblock editor does that)
//
// Initially, the form is filled in with either:
//      * values for the existing round we edit
//      * default initial values when creating a new round
//
// Form submits to controller_round_save_details().
// When a validation error occurs in controller_round_save_details() it calls
// this controller (controller_round_edit_details) as an error handler
// in order to display the form with the user-submitted data and their
// corresponding errors.
function controller_round_edit_details($round_id, $form_data = null,
                                       $form_errors = null) {
    global $identity_user;

    // ask for permissions
    $round = round_get($round_id);
    if ($round) {
        identity_require('round-edit', $round);
    }
    else {
        identity_require('round-create');
    }

    // validate round id
    if (!round_is_valid_id($round_id) && !$round) {
        flash_error('Identificatorul de runda este invalid');
        redirect(url(''));
    }

    // get parameter list for rounds (in general, not for this specific round)
    $param_list = round_get_parameter_infos_hack();
    // here we store parameter values
    $param_values = array();

    
    if (is_null($form_data)) {
        // initial form data (when displaying the form for the first time)
        $form_data = array();
        $form_errors = array();
        if (!$round) {
            // - default values (when creating a new round)
            $form_data['type'] = '';
            $form_data['active'] = '0';
            $form_data['tasks'] = array();

            // - default parameter values
            foreach ($param_list as $k => $v) {
                $param_values[$k] = $v['default'];
                $form_data['p_' . $k] = $v['default'];
            }
        }
        else {
            // - values from existing round
            $form_data['type'] = $round['type'];
            $form_data['active'] = $round['active'];

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
        // form was submitted. there was an error with the input
        // - $form_data already contains input data
        // - $form_errors already contains input errors

        // - extract parameter values from form_data.
        //   the `save` controller does a nice thing and extracts these values
        //   for convenience
        $param_values = $form_data['_param_values'];
    }

    // obtain task list
    $all_tasks = task_list_info();

    // view form
    $view = array();
    //  - choose title
    if (!$round) {
        $view['title'] = "Creaza runda: ".$round_id;
    }
    else {
        $view['title'] = "Modificare runda: ".$round_id;
    }
    //  - feed view values
    $view['action'] = url('admin/round/' . $round_id,
                          array('action' => 'save'));
    $view['form_values'] = $form_data;
    $view['form_errors'] = $form_errors;
    $view['param_list'] = $param_list;
    $view['param_values'] = $param_values;
    $view['all_tasks'] = $all_tasks;
    $view['page_name'] = 'admin/round/'.$round_id;
    execute_view_die("views/round_edit.php", $view);
}

// save controller
// Workflow is:
//      * controller_round_edit_details() displays form
//      * form submits to controller_round_save_details()
//      * controller_round_save_details() validates and uses
//        controller_round_edit_details() as error handler
function controller_round_save_details($round_id) {
    global $identity_user;

    // ask for permissions
    $round = round_get($round_id);
    if ($round) {
        identity_require('round-edit', $round);
    }
    else {
        identity_require('round-create');
    }

    // validate round id
    if (!round_is_valid_id($round_id) && !$round) {
        flash_error('Identificatorul de runda este invalid!');
        redirect(url(''));
    }

    // get parameter list for rounds (in general, not for this specific round)
    $param_list = round_get_parameter_infos_hack();

    // Validate data. Put incoming data in `data` and errors in `errors`
    $data = array();
    $errors = array();
    $data['type'] = getattr($_POST, 'type');
    $data['active'] = getattr($_POST, 'active');
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
    log_print("Task type is {$data['type']}");
    if (!in_array($data['type'], round_get_types())) {
        $errors['type'] = "Alegeti tipul rundei.";
    }
    // validate visibility
    if ('0' != $data['active'] && '1' != $data['active']) {
        $errors['active'] = "Valoare invalida";
    }
    if ('1' == $data['active'] && !identity_can('round-publish', $round)) {
        $errors['active'] = "Nu aveti permisiunea sa publicati runde.";
    }

    // validate parameter values
    if (in_array($data['type'], round_get_types())) {
        $p_errors = round_validate_parameters($data['type'], $param_values);
        if ($p_errors) {
            foreach ($p_errors as $k => $v) {
                $errors['p_' . $k] = $v;
            }
        }
    }

    // validate attached task list
    $all_tasks = task_list_info();
    $tasks = getattr($_POST, 'tasks', array());
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
            round_update($round_id, $data['type'], $data['active']);
            // note: updating a round does not change its owner (user_id)
        }
        else {
            round_create($round_id, $data['type'], $data['active'],
                         getattr($identity_user, 'id'));
        }
        // - update parameter values
        round_update_parameters($round_id, $param_values);
        // - update attached task list
        round_update_task_list($round_id, $tasks);
        // - done
        if ($round) {
            flash('Informatiile despre runda au fost salvate.');
            redirect(url($round['page_name']));
        }
        else {
            flash('O noua runda a fost creata. Acum trebuie sa editezi continutul...');
            redirect(url($round['page_name'], array('action'=>'edit')));
        }
    }
    else {
        // errors occured => call on error handler
        flash_error('Unul sau mai multe campuri au fost completate incorect!');
        controller_round_edit_details($round_id, $data, $errors);
    }
}
*/

?>
