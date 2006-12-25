<?php

require_once(IA_ROOT."common/db/round.php");
require_once(IA_ROOT."common/db/task.php");
require_once(IA_ROOT."common/round.php");

// Displays form to either create a new round or edit an existing one.
// This form does not edit round content (its associated textblock)
// (textblock editor does that)
//
// Initially, the form is filled in with either:
//      * values for the existing round we edit
//      * default initial values when creating a new round
//
// Form submits to controller_round_save_details().
// When a validation error occurs in controller_round_save_details() it calls
// this controller as an error handler in order to display the form
// with the user-submitted data and their corresponding errors.
function controller_round_details($round_id) {
    // validate round_id
    if (!is_round_id($round_id)) {
        flash_error('Identificatorul de round este invalid');
        redirect(url_home());
    }

    // Get round
    $round = round_get($round_id);
    if (!$round) {
        flash_error("Problema nu exista");
        redirect(url_home());
    }

    // Security check
    identity_require('round-edit', $round);

    // get parameter list for rounds (in general, not for this specific round)
    $param_infos = round_get_parameter_infos();
    $round_params = round_get_parameters($round['id']);

    // Form stuff.
    $values = array();
    $errors = array();

    // Fill in form values from request, defaults in $round
    $values['author'] = request('author', $round['author']);
    $values['type'] = request('type', $round['type']);
    $values['source'] = request('source', $round['source']);
    $values['hidden'] = request('hidden', $round['hidden']);
    $values['title'] = request('title', $round['title']);
    $values['page_name'] = request('page_name', $round['page_name']);

    // Parameter values, for all possible types of rounds.
    // Yucky, but functional.
    foreach (round_get_types() as $round_type) {
        foreach ($param_infos[$round_type] as $name => $info) {
            $form_name = "param_{$round_type}_{$name}";
            $def = $info['default'];
            if ($round_type == $round['type']) {
                $def = getattr($round_params, $name, $def);
            }
            $values[$form_name] = request($form_name, $def);
        }
    }

    // Validate the monkey.
    if (request_is_post()) {
        // Build new round
        $new_round = $round;
        $new_round['title'] = $values['title'];
        $new_round['page_name'] = $values['page_name'];
        $new_round['author'] = $values['author'];
        $new_round['source'] = $values['source'];
        $new_round['type'] = $values['type'];
        $new_round['hidden'] = $values['hidden'];

        $round_errors = round_validate($new_round);
        $errors = $round_errors;

        // Check security.
        if ($new_round['hidden'] != $round['hidden']) {
            identity_require('round-change-security', $round);
        }

        // Handle round parameters. Only for current type, and only if
        // properly selected.
        $new_round_params = $round_params;
        if (!array_key_exists('type', $round_errors)) {
            $round_type = $new_round['type'];
            foreach ($param_infos[$round_type] as $name => $info) {
                $form_name = "param_{$round_type}_{$name}";
                $new_round_params[$name] = $values[$form_name];
            }
            $round_params_errors = round_validate_parameters(
                    $round_type, $new_round_params);
            // Properly copy errors. Sucky
            foreach ($param_infos[$round_type] as $name => $info) {
                $form_name = "param_{$round_type}_{$name}";
                if (array_key_exists($name, $round_params_errors)) {
                    $errors[$form_name] = $round_params_errors[$name];
                }
            }
        }

        // If no errors then do the db monkey
        if (!$errors) {
            // FIXME: error handling? Is that even remotely possible in php?
            round_update_parameters($round_id, $new_round_params);
            round_update($new_round);

            flash("Task-ul a fost modificat cu succes.");
            redirect(url_round_edit($round_id));
        }
    }

    // Create view.
    $view = array();
    $view['title'] = "Editare $round_id";
    $view['page_name'] = url_round_edit($round_id);
    $view['round_id'] = $round_id;
    $view['round'] = $round;
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;
    $view['entity_types'] = round_get_types();
    $view['param_infos'] = $param_infos;

    execute_view_die("views/round_edit.php", $view);
}

// Creates a round. Minimalist
function controller_round_create()
{
    global $identity_user;

    // Security check. FIXME: sort of a hack.
    identity_require_login();
    identity_require("round-create",
            round_init_object('new_round', 'classic', $identity_user));

    // Form stuff.
    $values = array();
    $errors = array();

    // Get form values
    $values['id'] = request('id', '');
    $values['type'] = request('type', 'classic');

    if (request_is_post()) {
        if (!is_round_id($values['id'])) {
            $errors['id'] = "Id de round invalid";
        } else if (round_get($values['id'])) {
            $errors['id'] = "Exista deja un round cu acest id";
        }
        if (!in_array($values['type'], round_get_types())) {
            $errors['type'] = "Tip de round invalid";
        }

        if (!$errors) {
            $round = round_init_object(
                    $values['id'],
                    $values['type'],
                    $identity_user);
            $round_params = array();
            // FIXME: array_ magic?
            $param_infos = round_get_parameter_infos();
            foreach ($param_infos[$values['type']] as $name => $info) {
                $round_params[$name] = $info['default'];
            }

            // This should never fail.
            log_assert(round_create($round, $round_params));
            flash("O noua runda a fost creata, acum poti sa-l editezi");
            redirect(url_round_edit($round['id']));
        }
    }

    // Create view.
    $view = array();
    $view['title'] = "Creare runda";
    $view['page_name'] = url_round_create();
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;

    execute_view_die("views/round_create.php", $view);
}

?>

?>
