<?php

require_once(IA_ROOT_DIR."common/db/user.php");
require_once(IA_ROOT_DIR."common/user.php");

// Unsubscribe user $username from mailing list.
function controller_unsubscribe($username) {
    global $identity_user;

    $cpass = request('c');

    // validate username
    if ($username) {
        $user = user_get_by_username($username);
    }
    if (!$user) {
        flash_error('Numele de utilizator este invalid.');
        redirect(url_home());
    }

    // validate confirmation code
    if ($cpass != user_unsubscribe_key($user)) {
        flash_error('Codul de confirmare nu este corect!');
        redirect(url_home());
    }

    // check if user is already unsubscribed
    if (!getattr($user, 'newsletter')) {
        flash_error('Contul '.$user['username'].' NU este abonat la newsletter'
                    .'! Daca ai mai multe conturi pe aceeasi '
                    .'adresa de e-mail, va trebui sa de dezabonezi de la '
                    .'fiecare cont in parte!');
        redirect(url_home());
    }

    // unsubscribe user
    $fields = array(
        'newsletter' => 0 
    );
    user_update($fields, $user['id']);

    // update logged-in user
    if ($identity_user && $user['id'] == $identity_user['id']) {
        $user = user_get_by_id($user['id']);
        $identity_user = $user;
        identity_update_session($identity_user);
    }

    // notify yser
    flash('Ai fost dezabonat de la newsletter. O zi buna!');
    redirect(url_home());
}

?>
