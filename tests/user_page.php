#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR.'www/utilities.php');

test_cleanup();
test_prepare();

log_print("Anonymous tries to edit user page, fails");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1'),
));
log_assert_equal($res['url'],  url_absolute(url_login()));

log_print("Dude1 tries to edit user page, works");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1'),
        'user' => 'test_dude1',
));
log_assert_equal($res['url'],  url_absolute(
            url_textblock_edit('utilizator/test_dude1')));

log_print("Dude1 changes his user page");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1'),
        'user' => 'test_dude1',
        'post' => array(
            'text' => "h1. New xzx-content-xzx",
            'title' => "New xzx-title-xzx",
            'last_revision' => "1",
)));
log_assert_equal($res['url'], url_user_profile('test_dude1', true));

log_print("Anonymous looks at user page and sees changes");
$res = curl_test(array(
        'url' => url_textblock('utilizator/test_dude1'),
));
log_assert(strstr($res['content'], "xzx-content-xzx"));
log_assert(strstr($res['content'], "xzx-title-xzx"));
log_assert(stristr($res['content'], "<a href=\"".html_escape(
            url_user_profile('test_dude1'))));
log_assert(stristr($res['content'], "<a href=\"".html_escape(
            url_user_rating('test_dude1'))));
log_assert(stristr($res['content'], "<a href=\"".html_escape(
            url_user_stats('test_dude1'))));

log_print("Dude2 tries to edit dude1's page, fails");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude1'),
        'user' => 'test_dude2',
        'post' => array(
            'text' => "h1. New yzy-content-yzy",
            'title' => "New yzy-title-yzy",
            'last_revision' => "2",
)));
log_assert($res['url'] != url_absolute(
            url_textblock('utilizator/text_dude1')));

log_print("Dude2 can see Dude1's user page");
$res = curl_test(array(
        'url' => url_textblock('utilizator/test_dude1'),
        'user' => 'test_dude2',
));
log_assert(strstr($res['content'], "xzx-content-xzx"));
log_assert(strstr($res['content'], "xzx-title-xzx"));

log_print("Admin edits dude2's page");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude2'),
        'user' => 'test_admin',
        'post' => array(
            'text' => "New admin-content-admin",
            'title' => "New admin-title-admin",
            'last_revision' => "1",
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('utilizator/test_dude2')));
log_assert(strstr($res['content'], "admin-content-admin"));
log_assert(strstr($res['content'], "admin-title-admin"));

log_print("Admin tries to delete an user page, fails");
$res = curl_test(array(
        'url' => url_textblock_delete('utilizator/test_dude2'),
        'user' => 'test_admin',
        'post' => array()
));

log_print("Anon looks at dude2's page and it's still there.");
$res = curl_test(array(
        'url' => url_textblock('utilizator/test_dude2'),
));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('utilizator/test_dude2')));
log_assert(strstr($res['content'], "admin-content-admin"));
log_assert(strstr($res['content'], "admin-title-admin"));

log_print("Dude 2 tries to create another personal page, FAILS");
$res = curl_test(array(
        'url' => url_textblock_edit('utilizator/test_dude2/other-page'),
        'user' => 'test_dude2',
        'post' => array(
            'text' => "My xzx-other-title-xzx",
            'title' => "My xzx-other-content-xzx",
            'last_revision' => "0",
)));
log_assert($res['url'] != url_absolute(
            url_textblock('utilizator/test_dude2/other-page')));
log_assert(!strstr($res['content'], "xzx-other-title-xzx"));
log_assert(!strstr($res['content'], "xzx-other-content-xzx"));

log_print("Dude 2 tries to rename his user page to other-page-2, fails");
$res = curl_test(array(
        'url' => url_textblock_move('utilizator/test_dude2'),
        'user' => 'test_dude2',
        'post' => array(
            'new_page' => 'utilizator/test_dude2/other-page',
)));
log_assert($res['url'] != url_absolute(
            url_textblock('utilizator/test_dude2/other-page')));

log_print("User 2 tries to move his page to home. No haxoring");
$res = curl_test(array(
        'url' => url_textblock_move('utilizator/test_dude2'),
        'user' => 'test_dude2',
        'post' => array(
            'new_page' => 'home',
)));
log_assert($res['url'] != url_absolute(url_textblock('home')));

log_print("Anon checks main page is ok");
$res = curl_test(array(
        'url' => url_textblock('home'),
));
log_assert(!strstr($res['content'], "admin-title-admin"));
log_assert(!strstr($res['content'], "admin-content-admin"));

log_print("Anon checks dude2's page is ok");
$res = curl_test(array(
        'url' => url_textblock('utilizator/test_dude2'),
));
log_assert(strstr($res['content'], "admin-title-admin"));
log_assert(strstr($res['content'], "admin-content-admin"));
log_assert(stristr($res['content'], "<a href=\"".html_escape(
            url_user_profile('test_dude2'))));
log_assert(stristr($res['content'], "<a href=\"".html_escape(
            url_user_rating('test_dude2'))));
log_assert(stristr($res['content'], "<a href=\"".html_escape(
            url_user_stats('test_dude2'))));

test_cleanup();
log_print("User tests all passed");

?>
