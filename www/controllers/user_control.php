<?php

function controller_user_control() {
    if (!Identity::isAdmin()) {
        Util::redirectToHome();
    }

    $user_id = request('user_id');
    if (!$user_id) {
        Util::redirectToHome();
    }

    $user = user_get_by_id($user_id);
    if (!$user) {
        Util::redirectToHome();
    }

    $user['banned'] = !$user['banned'];
    user_update($user);
    redirect(url_user_profile($user['username']));
}
