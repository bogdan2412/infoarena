#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/config.php");
require_once(IA_ROOT_DIR . "common/common.php");
require_once(IA_ROOT_DIR . "common/log.php");
require_once(IA_ROOT_DIR . "common/db/smf.php");
require_once(IA_ROOT_DIR . "common/textblock.php");
require_once(IA_ROOT_DIR . "common/db/textblock.php");
require_once(IA_ROOT_DIR . "common/db/user.php");
db_connect();

$query = <<<SQL
SELECT *
    FROM `ia_user`
SQL;

$users = db_fetch_all($query);
foreach ($users as $user) {
    $tbname = IA_USER_TEXTBLOCK_PREFIX.'/'.$user['username'];
    if (is_normal_page_name($tbname)) {
        if (!textblock_get_revision($tbname)) {
            log_print("Missing textblock for {$user['username']}");
            $replace = array("user_id" => $user['username']);
            textblock_copy_replace("template/newuser", $tbname, $replace,
                    "public", $user['id']);
        }
    }
}

?>

