<?php

require_once(IA_ROOT_DIR."common/db/user.php");
require_once(IA_ROOT_DIR."www/format/format.php");

// Displays user information.
// Includes avatar, etc.
//
// Args:
//      user(required): user id.
//      info(required): parameter.
function macro_userinfo($args) {
    $user_id = getattr($args, 'user', '');
    if ($user_id === '') {
        return macro_error("User parameter required.");
    }

    $info = getattr($args, 'info', '');
    if ($info === '') {
        return macro_error("Info parameter required");
    }

    $user = user_get_by_username($user_id);
    if (!$user) {
        return macro_error("No such username: ".$user_id);
    }

    switch ($info) {
        case 'email':
            // FIXME: display e-mail only for admins
            return macro_error("Adresa de email este ascunsa");
        case 'fullname':
            return html_escape($user['full_name']);
        case 'username':
            return html_escape($user['username']);
        case 'security':
            $custom_swag = array(
                'bogdan2412' => 'Technical lead',
                'andrei.12' => 'Administrator alcoolic');
            if (array_key_exists($user['username'], $custom_swag)) {
                return $custom_swag[$user['username']];
            }
            switch ($user['security_level']) {
            case 'admin':
                return 'Administrator';
            case 'normal':
                return 'Utilizator normal';
            default:
                return html_escape(ucfirst($user['security_level']));
            }
        case 'rating':
            if ($user['rating_cache']) {
                return html_escape(rating_scale($user['rating_cache']));
            } else {
                return 'n/a';
            }
        default:
            return macro_error("Invalid info paramater");
    }
}
