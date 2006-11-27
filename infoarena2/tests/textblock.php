#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

// New page with no user, redirect to login.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_view('sandbox/test_page', true),
));
log_assert($res['url'] == url_login(true));

// New page with some user, redirect to create
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_view('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
log_assert($res['url'] == url_textblock_edit('sandbox/test_page', true));

// dude1 creates page
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'form_text' => "Test page\nxzx-content1-xzx\n",
                'form_title' => "Test xzx-title1-xzx",
)));
log_assert($res['url'] == url_textblock_view('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content1-xzx'));
log_assert(strstr($res['content'], 'xzx-title1-xzx'));

// Admin makes edit #2 and protects page.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'form_text' => "Test page\nxzx-content2-xzx\n",
                'form_title' => "Test xzx-title2-xzx",
                'form_security' => "protected",
)));
log_assert($res['url'] == url_textblock_view('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));

// dude1 tries to modify page, doesn't work.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'form_text' => "Test page\nxzx-contentF-xzx\n",
                'form_title' => "Test xzx-titleF-xzx",
)));
log_assert($res['url'] != url_textblock_view(true));
log_assert(!strstr($res['content'], 'xzx-content2-xzx'));
log_assert(!strstr($res['content'], 'xzx-title2-xzx'));

// Anon sees admin version.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_view('sandbox/test_page', true),
));
log_assert($res['url'] == url_textblock_view('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));

log_print("Textblock tests all passed");
test_cleanup();

?>
