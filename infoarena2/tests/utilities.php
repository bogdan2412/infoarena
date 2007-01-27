<?php

require_once(dirname($argv[0]) . "/../config.php");
require_once(IA_ROOT_DIR."common/log.php");
require_once(IA_ROOT_DIR."common/common.php");
require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/user.php");

// Test with curl. $args format:
// * url: url to curl. If http:// is ommited IA_URL_HOST is assumed.
// * post: post arguments (use url_ functions for get args).
// * user: user to curl as. HTTP auth is used, password is always pwd.
// * validate_html: boolean, default true. Automatic html validation.
//
// TODO: random passwords in test_prepare.
function curl_test($args)
{
    $curl_args = array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_VERBOSE => true,
    );

    if (strpos('http://', $args['url']) === false) {
        $curl_args[CURLOPT_URL] = IA_URL_HOST . $args['url'];
    } else {
        $curl_args[CURLOPT_URL] = $args['url'];
    }

    // Properly encode POSTFIELDS
    if (isset($args['post'])) {
        $post_args = array();
        // Properly encode array args. Fucking awesome
        // FIXME: does not properly handle multiple levels.
        // FIXTHEM: people who use that deserve to die.
        foreach ($args['post'] as $name => $val) {
            if (is_array($val)) {
                // Oh yeah, pure evil baby.
                foreach ($val as $k => $v) {
                    $post_args["{$name}[{$k}]"] = $v;
                }
            } else {
                $post_args[$name] = $val;
            }
        }
        $curl_args[CURLOPT_POSTFIELDS] = $post_args;
    }

    if (isset($args['user'])) {
        $curl_args[CURLOPT_USERPWD] = $args['user'].':pwd';
    }

    $ch = curl_init();
    curl_setopt_array($ch, $curl_args);

    $content = curl_exec($ch);
    if ($content === false) {
        log_print_r($curl_args);
        log_error("Failed curling.");
    }
    $res = curl_getinfo($ch);
    $res['content'] = $content;
    curl_close($ch);
    
    if (getattr($args, 'validate_html', true)) {
        validate_html($content);
    }

    return $res;
}

// Validates a string as a html document.
// Uses the external "validate" tool, packages wdg-html-validator in debian.
// dies on failure.
function validate_html($content)
{
    $fname = IA_ROOT_DIR . 'tests/temp.html';
    file_put_contents($fname, $content);

    $result = shell_exec("validate --warn --verbose $fname");

    if (strstr($result, 'Error') || strstr($result, 'Warning')) {
        log_print($result);
        log_error("HTML validation failed");
    }
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
            'username' => 'test_helper1',
            'password' => 'pwd',
            'full_name' => 'Testing Helper 1',
            'email' => 'no@spam.com',
            'security_level' => 'helper',
    )), "Failed creating test helper 1");

    log_assert(user_create(array(
            'username' => 'test_helper2',
            'password' => 'pwd',
            'full_name' => 'Testing Helper 2',
            'security_level' => 'helper',
    )), "Failed creating test helper 2");

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
// However, tests are only run on a local copy anyway.
function test_cleanup()
{
    db_query("DELETE FROM ia_user WHERE `username` LIKE 'test_%'");
    db_query("DELETE FROM ia_task WHERE `id` LIKE 'test_%'");
    db_query("DELETE FROM ia_round WHERE `id` LIKE 'test_%'");
    // Remove various stuff from the wiki.
    $prefixes = array('sandbox', 'utilizator', 'runda', 'problema');
    foreach ($prefixes as $prefix) {
        db_query("DELETE FROM `ia_textblock` ".
                 "WHERE `name` LIKE '$prefix/test_%'");
        db_query("DELETE FROM `ia_textblock_revision` ".
                 "WHERE `name` LIKE '$prefix/test_%'");
        db_query("DELETE FROM `ia_file` ".
                 "WHERE `page` LIKE '$prefix/test_%'");
    }
}

db_connect();
check_requirements();

// Add log timestamps.
define("IA_LOG_TIMESTAMP_FORMAT", "Y-m-d H:i:s");

?>
