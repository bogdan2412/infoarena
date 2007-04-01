#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/config.php");
require_once(IA_ROOT_DIR . "common/common.php");
require_once(IA_ROOT_DIR . "common/log.php");
require_once(IA_ROOT_DIR . "common/db/smf.php");
require_once(IA_ROOT_DIR . "common/db/user.php");
db_connect();

$query = <<<SQL
SELECT *
    FROM `ia_user`
    WHERE 0 = (
        SELECT COUNT(*)
            FROM `ia_smf_members`
            WHERE `memberName` = `username`
    )
SQL;

$users = db_fetch_all($query);
foreach ($users as $user) {
    log_print_r($user);
    //smf_create_user($user);
}

?>

