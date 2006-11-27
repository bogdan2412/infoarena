<?php

require_once(IA_ROOT."common/db/round.php");

// Disabled.
/*
// Displays form to register remote user to given round
function controller_round_register($round_id) {
    global $identity_user;
    $round = round_get($round_id);
    $submit = request('action', '') == 'submit';

    // check round & permissions
    if ($round) {
        identity_require('round-register', $round);
    }
    else {
        flash_error('Runda specificata nu exista in baza de date!');
        redirect(url());
    }

    // check whether user is already registered
    log_assert($identity_user);
    if (round_is_registered($round['id'], $identity_user['id'])) {
        flash_error('Sunteti deja inregistrat in runda <em>'.htmlentities($round['title']).'</em>!');
        redirect(url($round['page_name']));
    }

    if ($submit) {
        // process input
        round_register_user($round['id'], $identity_user['id']);
        // FIXME: should redirect to referrer
        flash('Ati fost inregistrat la <em>'.htmlentities($round['title']).'</em>!');
        redirect(url($round['page_name']));
    }
    else {
        // display form
        $view = array(
            'round' => $round,
            'title' => 'Inregistrare la "'.$round['title'].'"',
            'action' => url('round_register/'.$round['id'], array('action'=>'submit'))
        );
        execute_view_die('views/round_register.php', $view);
    }
}*/

?>
