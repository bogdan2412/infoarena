<?php

require_once(IA_ROOT_DIR."common/db/round.php");
require_once(IA_ROOT_DIR."www/identity.php");
require_once(IA_ROOT_DIR."www/format/pager.php");

// Displays form to register remote user to given round_id
function controller_round_register($round_id) {
    global $identity_user;

    if (!is_round_id($round_id)) {
        flash_error("Identificatorul rundei este invalid");
        redirect(url_home());
    }
    $round = round_get($round_id);
    $submit = request_is_post();

    // check round_id & permissions
    if ($round) {
        identity_require('round-register', $round);
    }
    else {
        flash_error('Runda specificata nu exista in baza de date!');
        redirect(url_home());
    }

    // check whether user is already registered
    if (round_is_registered($round['id'], $identity_user['id'])) {
        flash_error('Esti deja inregistrat in runda "'.$round['title'].'"!');
        redirect(url_textblock($round['page_name']));
    }

    if ($submit) {
        // process input
        round_register_user($round['id'], $identity_user['id']);
        // FIXME: should redirect to referrer
        flash('Ai fost inregistrat la "'.$round['title'].'"!');
        redirect(url_textblock($round['page_name']));
    }
    else {
        // display form
        $view = array(
            'round' => $round,
            'title' => 'Inregistrare la '.$round['title'],
            'action' => url_round_register($round['id']),
        );
        execute_view_die('views/round_register.php', $view);
    }
}

// Displays registered users to given round_id
function controller_round_register_view($round_id) {
    if (!is_round_id($round_id)) {
        flash_error("Identificatorul rundei este invalid");
        redirect(url_home());
    }
    $round = round_get($round_id);

    // check round_id & permissions
    if ($round) {
        identity_require('round-register-view', $round);
    }
    else {
        flash_error('Runda specificata nu exista in baza de date!');
        redirect(url_home());
    }

    $options = pager_init_options();
    $view = array();
    $view['title'] = 'Utiliztori inregistrati la '.$round['title'];
    $view['round'] = $round;
    $view['users'] = round_get_registered_users_range($round['id'], 
                     $options['first_entry'], $options['display_entries']);
    $view['first_entry'] = $options['first_entry'];
    $view['total_entries'] =  round_get_registered_users_count($round['id']);
    $view['display_entries'] = $options['display_entries'];

    execute_view_die('views/round_register_view.php', $view);
}

?>
