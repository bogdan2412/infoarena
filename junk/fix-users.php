#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/config.php");
require_once(IA_ROOT_DIR . "common/common.php");
require_once(IA_ROOT_DIR . "common/log.php");
require_once(IA_ROOT_DIR . "common/db/smf.php");
require_once(IA_ROOT_DIR . "common/db/user.php");
require_once(IA_ROOT_DIR . "common/db/textblock.php");
db_connect();

$query = <<<SQL
SELECT *
    FROM `ia_user`
SQL;

$users = db_fetch_all($query);
$full_name_errors = 0;
$email_errors = 0;
$username_errors = 0;
foreach ($users as $user) {
    $chg = false;
    $old_user = $user;

    if (!is_user_name($user['username'])) {
        $new_username = preg_replace('/[^a-z_\.\-\@]/i', '_', $user['username']);
        $user['username'] = $new_username;
        $chg = true;
        ++$username_errors;
    }

    if (!is_valid_email($user['email'])) {
        $user['email'] = $user['username'] . '@necunoscut.com';
        $chg = true;
        ++$email_errors;
    }

    if (!is_user_full_name($user['full_name'])) {
        $user['full_name'] = "Unknown";
        $chg = true;
        ++$full_name_errors;
    }

    if ($chg) {
        log_assert_valid(user_validate($user));
    }
}
log_print("full_name: $full_name_errors");
log_print("email: $email_errors");
log_print("username: $username_errors");

?>

