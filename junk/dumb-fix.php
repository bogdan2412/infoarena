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

mem_cache_purge();
$date1 = db_date_format(mktime(18, 20, 0, 2, 10, 2007));
$date2 = db_date_format(mktime(18, 25, 0, 2, 10, 2007));

$users = user_get_all();
$template = textblock_get_revision('template/newuser');
foreach ($users as $user) {
    $tbname = IA_USER_TEXTBLOCK_PREFIX.$user['username'];
    if (!is_normal_page_name($tbname)) {
        log_print("{$user['username']} is a luzer");
        continue;
    }
    $quote_tbname = db_quote($tbname);
    $bad_tb = $template;
    $replace = array("user_id" => $user['username']);
    textblock_template_replace($bad_tb, $replace);

    $quo_title = db_quote($bad_tb['title']);
    $quo_text = db_quote($bad_tb['text']);
    $query = <<<SQL
DELETE        
    FROM ia_textblock_revision
    WHERE timestamp > '$date1' AND
          timestamp < '$date2' AND
          name = $quote_tbname AND
          title = $quo_title AND
          text = $quo_text
SQL;
}   

?>

