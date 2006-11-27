<?php

require_once(dirname($argv[0]) . "/../config.php");
require_once(IA_ROOT."common/log.php");
require_once(IA_ROOT."common/db/db.php");
require_once(IA_ROOT."common/db/user.php");
require_once(IA_ROOT."common/common.php");

function quick_curl($args)
{
    $args = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
    ) + $args;

    // Properly encode POSTFIELDS
    if (is_array($args[CURLOPT_POSTFIELDS])) {
        $encval = '';
        foreach ($args[CURLOPT_POSTFIELDS] as $k => $v) {
            $encval .= "$k=" . urlencode($v) ."&";
        }
        $args[CURLOPT_POSTFIELDS] = substr($encval, 0, -1);
    }

    $ch = curl_init();
    curl_setopt_array($ch, $args);

    $content = curl_exec($ch);
    if ($content === false) {
        log_error("Failed curling.");
    }
    $res = curl_getinfo($ch);
    $res['content'] = $content;
    curl_close($ch);

    /*
    log_print("Curling args:");
    log_print_r($args);
    log_print("Curling res:");
    log_print_r($res);
    log_print("");
    */

    $weberror = null;

    // Check php parser error, missing functions, etc.
    if (!strstr($res['content'], '<html xmlns="http://www.w3.org/1999/xhtml">')) {
        $weberror = $res['content'];
    }

    // Check error. This catches a normal www error
    if (preg_match('/  \<pre\ class\="debug-error"\>  (.*)  \<\/pre\>  /sxi', $res['content'], $matches)) {
        $weberror = $matches[1];
    }

    // FIXME: JSON doesn't print <html, better checks?
    // FIXME: check valid xml? evil.

    if (!is_null($weberror)) {
        log_print("\nWebsite made a boo boo:");
        log_print($weberror);
        die();
    }

    return $res;
}

// Create test users.
function test_prepare()
{
    log_assert(user_create(array(
            'username' => 'test_dude1',
            'password' => 'pwd',
            'full_name' => 'Testing Dude 1',
            'email' => 'no@spam.com',
    )), "Failed creating test dude 1");

    log_assert(user_create(array(
            'username' => 'test_dude2',
            'password' => 'pwd',
            'full_name' => 'Testing Dude 2',
            'email' => 'no@spam.com',
    )), "Failed creating test dude 2");

    log_assert(user_create(array(
            'username' => 'test_admin',
            'password' => 'pwd',
            'full_name' => 'Testing Admin',
            'email' => 'no@spam.com',
            'security_level' => 'admin',
    )), "Failed creating test admin");
}

// Cleanup for testing.
// Warning: bugs might fuck the db.
function test_cleanup()
{
    db_query("DELETE FROM ia_user WHERE `username` LIKE 'test_%'");
    db_query("DELETE FROM ia_task WHERE `id` LIKE 'test_%'");
    db_query("DELETE FROM ia_round WHERE `id` LIKE 'test_%'");
    db_query("DELETE FROM ia_textblock WHERE `name` LIKE 'sandbox/test_%'");
    db_query("DELETE FROM ia_textblock_revision WHERE `name` LIKE 'sandbox/test_%'");
    db_query("DELETE FROM ia_file WHERE `page` LIKE 'soundbox/test_%'");
}

?>
