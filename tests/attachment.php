#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR.'www/utilities.php');

test_cleanup();
test_prepare();

log_print("Dude 1 creates a page");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_dude1',
        'post' => array(
                'text' => "Test page\nxzx-content1-xzx\n",
                'title' => "Test xzx-title1-xzx",
                'last_revision' => "0",
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
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page')));

log_print("Anon looks at file");
$res = curl_test(array(
        'url' => url_attachment('sandbox/test_page', 'test_file'),
        'validate_html' => false,
));
log_assert_equal($res['content'],  'xzx-file-xzx');

log_print("Anon looks at attachment list");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page'),
));
log_assert(strstr($res['content'], html_escape(
        url_attachment('sandbox/test_page', 'test_file'))));
// FIXME: this shouldn't be visible
log_assert(strstr($res['content'], html_escape(
            url_attachment_delete('sandbox/test_page', 'test_file'))));

log_print("Anon tries to delete attachment, fails");
$res = curl_test(array(
        'url' => url_attachment_delete('sandbox/test_page', 'test_file'),
        'post' => array(),
));
log_assert_equal($res['url'],  url_absolute(url_login()));

log_print("Admin looks in list and sees attachment");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page'),
));
log_assert(strstr($res['content'], html_escape(
            url_attachment('sandbox/test_page', 'test_file'))));

log_print("Admin deletes attachment, OK");
$res = curl_test(array(
        'url' => url_attachment_delete('sandbox/test_page', 'test_file'),
        'user' => 'test_admin',
        'post' => array()
));
log_assert_equal($res['url'],  url_absolute(url_textblock('sandbox/test_page')));

log_print("Admin looks in list and attachment is gone");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page', 'test_file'),
));
log_assert(!strstr($res['content'], html_escape(
            url_attachment('sandbox/test_page', 'test_file'))));

log_print("Dude 1 attaches 5 files");
for ($i = 1; $i <= 5; ++$i) {
    // FIXME: easily borked.
    file_put_contents("/tmp/test_file_$i", "xzx-spam-file-$i-xzx");
    $res = curl_test(array(
            'url' => url_attachment_new('sandbox/test_page'),
            'user' => 'test_dude1',
            'post' => array(
                    'file_name' => "@/tmp/test_file_$i"),
    ));
    log_assert_equal($res['url'],  url_absolute(
                url_textblock('sandbox/test_page')));
}

log_print("Dude 1 tries to overwrite file 3");
file_put_contents('/tmp/test_file_3', 'xzx-spam-file-3-replaced-xzx');
$res = curl_test(array(
        'url' => url_attachment_new('sandbox/test_page'),
        'user' => 'test_dude1',
        'post' => array(
                'file_name' => '@/tmp/test_file_3'),
));
log_assert_equal($res['url'], url_absolute(url_home()));

log_print("Overwriting failed");
$res = curl_test(array(
        'url' => url_attachment('sandbox/test_page', 'test_file_3'),
        'validate_html' => false,
));
log_assert_equal($res['content'],  'xzx-spam-file-3-xzx');
log_assert($res['content'] != 'xzx-spam-file-3-replaced-xzx');

log_print("Dude 2 tries to overwrites file 1");
file_put_contents('/tmp/test_file_1', 'xzx-spam-file-1-replaced-xzx');
$res = curl_test(array(
        'url' => url_attachment_new('sandbox/test_page'),
        'user' => 'test_dude2',
        'post' => array(
                'file_name' => '@/tmp/test_file_1'),
));
log_assert_equal($res['url'],  url_absolute(url_home()));

log_print("Anon sees original file");
$res = curl_test(array(
        'url' => url_attachment('sandbox/test_page', 'test_file_1'),
        'validate_html' => false,
));
log_assert_equal($res['content'],  'xzx-spam-file-1-xzx');

log_print("Admin looks at file list and is horrified at the spamming from dude1");
$res = curl_test(array(
        'url' => url_attachment_list('sandbox/test_page'),
        'user' => 'test_admin',
));
for ($i = 1; $i <= 5; ++$i) {
    log_assert(strstr($res['content'], html_escape(
                url_attachment('sandbox/test_page', "test_file_$i"))));
}
log_assert(strstr(strtolower($res['content']), html_escape(
            url_user_profile('test_dude1'))));
log_assert(!strstr(strtolower($res['content']), html_escape(
            url_user_profile('test_dude2'))));

log_print("Admin moves the spammed page.");
$res = curl_test(array(
        'url' => url_textblock_move('sandbox/test_page'),
        'user' => 'test_admin',
        'post' => array(
            'new_name' => 'sAnDbox/test_page_2',
)));
log_assert_equal($res['url'], url_absolute(url_textblock('sandbox/test_page_2')));

log_print("Anon sees the attachments stayed and he complains");
$res = curl_test(array(
        'url' => url_attachment('sandbox/test_page_2', 'test_file_2'),
        'validate_html' => false,
));
log_assert_equal($res['content'],  'xzx-spam-file-2-xzx');

log_print("Admin deletes page");
$res = curl_test(array(
        'url' => url_textblock_delete('sandbox/test_page_2'),
        'user' => 'test_admin',
        'post' => array()
));
log_assert_equal($res['url'], url_absolute(url_home()));

log_print("Anon sees spam is finally gone");
$res = curl_test(array(
        'url' => url_attachment('sandbox/test_page_2', 'test_file_2'),
        'validate_html' => false,
));
log_assert($res['content'] != 'xzx-spam-file-2-xzx');
log_assert_equal($res['http_code'], 404);

log_print("Check files are gone in the db and on disk");
log_assert_equal(array(), attachment_get_all('sandbox/test_page_2'));
log_assert_equal(0, attachment_get_count('sandbox/test_page_2'));
log_assert_equal(array(), glob(IA_ROOT_DIR . "attach/sandbox_test_page*"));

log_print("All tests passed. Warning: incomplete.");
test_cleanup();
