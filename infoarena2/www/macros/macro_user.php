<?php

require_once(IA_ROOT . 'www/format/format.php');
require_once(IA_ROOT . 'common/db/user.php');

// Display a link to an user.
// Includes avatar, etc.
//
// Args:
//      user(required): user id.
//      type: link(default), tiny, normal, etc.
function macro_user($args) {
    $user = getattr($args, 'user', '');
    if ($user === '') {
        return macro_error("User parameter required.");
    }

    $dbuser = user_get_by_username($user);
    if (!$dbuser) {
        return macro_error("User inexistent.");
    }

    $type = getattr($args, 'type', 'link');
    if ($type == 'link') {
        return format_user_link($dbuser['username'], $dbuser['full_name']);
    } else if ($type == 'tiny') {
        return format_user_tiny($dbuser['username'], $dbuser['full_name']);
    } else if ($type == 'normal') {
        return format_user_normal($dbuser['username'], $dbuser['full_name']);
    } else {
        return macro_error("Unknown userlink type\"$type\"");
    }
}

?>
