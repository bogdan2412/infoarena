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
log_assert($res['url'] == url_login(true),
        "Redirect to login page.");

// New page with some user, redirect to create
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_view('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
log_assert($res['url'] == url_textblock_edit('sandbox/test_page', true),
        "Redirect to create page");

$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'form_text' => urlencode("Test page\nversion 1\n"),
                'title' => urlencode("Test page 1"),
        ),
));
log_assert($res['url'] == url_textblock_view('sandbox/test_page', true),
        "Redirect to newly created page");

log_print("Textblock tests all passed");
test_cleanup();

?>
