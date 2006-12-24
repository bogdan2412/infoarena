<?php

require_once(dirname($argv[0]) . "/../config.php");
require_once(IA_ROOT."common/log.php");
require_once(IA_ROOT."common/common.php");
require_once(IA_ROOT."common/db/db.php");
require_once(IA_ROOT."common/db/user.php");

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
/*        $encval = '';
        foreach ($args['post'] as $k => $v) {
            $args[$k] = urlencode($v);
            // Hack for @, used for files. It shouldn't get encoded.
            if (strlen($v) < 1 || $v[0] != '@') {
                $v = urlencode($v);
            }
            $encval .= "$k=" . $v ."&";
        }
        $curl_args[CURLOPT_POSTFIELDS] = substr($encval, 0, -1); */
        $curl_args[CURLOPT_POSTFIELDS] = $args['post'];
        //$curl_args[CURLOPT_POST] = true;
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
    $fname = IA_ROOT . 'tests/temp.html';
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
function test_cleanup()
{
    db_query("DELETE FROM ia_user WHERE `username` LIKE 'test_%'");
    db_query("DELETE FROM ia_task WHERE `id` LIKE 'test_%'");
    db_query("DELETE FROM ia_round WHERE `id` LIKE 'test_%'");
    db_query("DELETE FROM ia_textblock WHERE `name` LIKE 'sandbox/test_%'");
    db_query("DELETE FROM ia_textblock_revision WHERE `name` LIKE 'sandbox/test_%'");
    db_query("DELETE FROM ia_textblock WHERE `name` LIKE 'utilizator/test_%'");
    db_query("DELETE FROM ia_textblock_revision WHERE `name` LIKE 'utilizator/test_%'");
    db_query("DELETE FROM ia_file WHERE `page` LIKE 'sandbox/test_%'");
}

?>
