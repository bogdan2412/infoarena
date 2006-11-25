<?php

// Displays user information.
// Includes avatar, etc.
//
// Args:
//      user(required): user id.
//      info(required): parameter.
function macro_userinfo($args) {
    static $last_user_id = null;
    static $user;

    $user_id = getattr($args, 'user', '');
    if ($user_id === '') {
        return macro_error("User parameter required.");
    }

    if ($last_user_id != $user_id) {
        $user = user_get_by_username($user_id);
        if (!$user) {
            return macro_error("User inexistent.");
        }
    }

    $info = getattr($args, 'info', '');
    if ($info === '') {
        return macro_error("Info parameter required");
    }

    switch ($info) {
        case 'email':
            // FIXME: private email?
            return $user['email'];
        case 'fullname':
            return $user['full_name'];
        case 'username':
            return $user['username'];
        case 'security':
            return $user['security_level'];
        default:
            return macro_error("Invalid info paramater");
    }
}
?>
