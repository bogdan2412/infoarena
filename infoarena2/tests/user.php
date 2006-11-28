#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

// Anon try to edit user page.
// Fail, redirect to login.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('utilizator/test_dude1', true),
));
validate_html($res['content']);
log_assert($res['url'] == url_login(true));

// Try to edit user page.
// No problem
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('utilizator/test_dude1', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock_edit('utilizator/test_dude1', true));

// Modify own profile page.
// Success
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('utilizator/test_dude1', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'text' => "h1. New xzx-content-xzx",
            'title' => "New xzx-title-xzx",
)));
validate_html($res['content']);
log_assert($res['url'] == url_user_profile('test_dude1', true));

// Anon try to view user page.
// Sees changes.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('utilizator/test_dude1', true),
));
validate_html($res['content']);
log_assert(strstr($res['content'], "xzx-content-xzx"));
log_assert(strstr($res['content'], "xzx-title-xzx"));

// Other tries to modify user profile page.
// Redirected to login.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('utilizator/test_dude1', true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'text' => "h1. New yzy-content-yzy",
            'title' => "New yzy-title-yzy",
)));
validate_html($res['content']);
log_assert($res['url'] != url_textblock('utilizator/text_dude1'));

// Dude tries to view user page.
// OK, but his changes failed.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('utilizator/test_dude1', true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
));
validate_html($res['content']);
log_assert(strstr($res['content'], "xzx-content-xzx"));
log_assert(strstr($res['content'], "xzx-title-xzx"));

// Admin edits dude2's page.
// With impunity
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('utilizator/test_dude2', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
            'text' => "New admin-content-admin",
            'title' => "New admin-title-admin",
)));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('utilizator/test_dude2', true));
log_assert(strstr($res['content'], "admin-content-admin"));
log_assert(strstr($res['content'], "admin-title-admin"));

test_cleanup();

log_print("User tests all passed");

?>
