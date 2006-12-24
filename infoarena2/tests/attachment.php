#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

log_print("Dude 1 creates a page");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_dude1',
        'post' => array(
                'text' => "Test page\nxzx-content1-xzx\n",
                'title' => "Test xzx-title1-xzx",
)));

log_print("Dude 1 attaches a sample file");
// FIXME: easily borked.
file_put_contents('/tmp/test_file', 'xzx-file-xzx');
$res = curl_test(array(
        'url' => url_attachment_new('sandbox/test_page'),
        'user' => 'test_dude1',
        'post' => array(
                'file_name' => '@/tmp/test_file'),
));
log_assert($res['url'] == url_absolute(
            url_textblock('sandbox/test_page')));

log_print("Anon looks at file");
$res = curl_test(array(
        'url' => url_attachment('sandbox/test_page', 'test_file'),
        'validate_html' => false,
));
log_assert($res['content'] == 'xzx-file-xzx');

log_print("Anon looks at attachment list");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page'),
));
log_assert(strstr($res['content'], htmlentities(
        url_attachment('sandbox/test_page', 'test_file'))));
// FIXME: this shouldn't be visible
log_assert(strstr($res['content'], htmlentities(
            url_attachment_delete('sandbox/test_page', 'test_file'))));

log_print("Anon tries to delete attachment, fails");
$res = curl_test(array(
        'url' => url_attachment_delete('sandbox/test_page', 'test_file'),
));
log_assert($res['url'] == url_absolute(url_login()));

log_print("Admin looks in list and sees attachment");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page', 'test_file'),
));
log_assert(strstr($res['content'], htmlentities(
            url_attachment('sandbox/test_page', 'test_file'))));

log_print("Admin deletes attachment, OK");
$res = curl_test(array(
        'url' => url_attachment_delete('sandbox/test_page', 'test_file'),
        'user' => 'test_admin',
));
log_assert($res['url'] == url_absolute(url_textblock('sandbox/test_page')));

log_print("Admin looks in list and attachment is gone");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page', 'test_file'),
));
log_assert(!strstr($res['content'], htmlentities(
            url_attachment('sandbox/test_page', 'test_file'))));

log_print("All tests passed. Warning: incomplete.");

test_cleanup();
