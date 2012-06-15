<?php
require_once(IA_ROOT_DIR . 'www/format/format.php');
require_once(IA_ROOT_DIR . 'common/db/user.php');
/**
 * Returns an image showing user statistics.
 *
 * @param string $username
 * @return string Rendered HTML
 *
 */

function macro_userwidget($args) {
    $user = getattr($args, 'user', '');
    if ($user === '') {
        return macro_error("User parameter required.");
    }
    $dbuser = user_get_by_username($user);
    if (!$dbuser) {
        return macro_error("User inexistent.");
    }
    $ret = '<img src="';
    $ret .= url_userwidget($user);
    $ret .= '" />';
    return $ret;
}
