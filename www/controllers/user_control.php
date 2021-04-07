<?php

function controller_user_control() {
    if (!identity_is_admin()) {
        redirect(url_home());
    }

    $user_id = request('user_id');
    if (!$user_id) {
        redirect(url_home());
    }

    $user = user_get_by_id($user_id);
    if (!$user) {
        redirect(url_home());
    }

    $user['banned'] = !$user['banned'];
    user_update($user);
    redirect(url_user_profile($user['username']));
}
