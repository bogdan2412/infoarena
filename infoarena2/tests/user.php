#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

log_print("Anonymous tries to edit user page, fails");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1', true),
));
log_assert($res['url'] == url_absolute(url_login()));

log_print("Dude1 tries to edit user page, works");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1', true),
        'user' => 'test_dude1',
));
log_assert($res['url'] == url_absolute(
            url_textblock_edit('utilizator/test_dude1', true)));

log_print("Dude1 changes his user page");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1', true),
        'user' => 'test_dude1',
        'post' => array(
            'text' => "h1. New xzx-content-xzx",
            'title' => "New xzx-title-xzx",
)));
log_assert($res['url'] == url_absolute(
            url_user_profile('test_dude1', true)));

log_print("Anonymous looks at user page and sees changes");
$res = curl_test(array(
        'url' => url_textblock('utilizator/test_dude1', true),
));
log_assert(strstr($res['content'], "xzx-content-xzx"));
log_assert(strstr($res['content'], "xzx-title-xzx"));

log_print("Dude2 tries to edit dude1's page, fails");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1', true),
        'user' => 'test_dude2:pwd',
        'post' => array(
            'text' => "h1. New yzy-content-yzy",
            'title' => "New yzy-title-yzy",
)));
log_assert($res['url'] != url_absolute(
            url_textblock('utilizator/text_dude1')));

log_print("Dude2 can see Dude1's user page");
$res = curl_test(array(
        'url' => url_textblock('utilizator/test_dude1', true),
        'user' => 'test_dude2',
));
log_assert(strstr($res['content'], "xzx-content-xzx"));
log_assert(strstr($res['content'], "xzx-title-xzx"));

log_print("Admin edits dude2's page");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude2', true),
        'user' => 'test_admin',
        'post' => array(
            'text' => "New admin-content-admin",
            'title' => "New admin-title-admin",
)));
log_assert($res['url'] == url_absolute(
            url_textblock('utilizator/test_dude2', true)));
log_assert(strstr($res['content'], "admin-content-admin"));
log_assert(strstr($res['content'], "admin-title-admin"));

test_cleanup();
log_print("User tests all passed");

?>
