<?php
require_once(Config::ROOT . 'www/format/format.php');
require_once(Config::ROOT . 'common/db/user.php');
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
        return macro_error("Utilizator inexistent.");
    }
    $ret = '<img src="';
    $ret .= url_absolute(IA_URL_PREFIX . "userwidget/" . $user);
    $ret .= '">';
    return $ret;
}
?>
