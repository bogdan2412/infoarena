#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

// New page with no user, redirect to login.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
));
log_assert($res['url'] == url_login(true));

// New page with some user, redirect to create
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
log_assert($res['url'] == url_textblock_edit('sandbox/test_page', true));

// dude1 creates page
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content1-xzx\n",
                'title' => "Test xzx-title1-xzx",
)));
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content1-xzx'));
log_assert(strstr($res['content'], 'xzx-title1-xzx'));

// Admin makes edit #2 and protects page.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content2-xzx\n",
                'title' => "Test xzx-title2-xzx",
                'security' => "protected",
)));
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));

// dude1 tries to modify page, doesn't work.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-contentF-xzx\n",
                'title' => "Test xzx-titleF-xzx",
)));
log_assert($res['url'] != url_textblock(true));
log_assert(!strstr($res['content'], 'xzx-content2-xzx'));
log_assert(!strstr($res['content'], 'xzx-title2-xzx'));

// Anon sees admin version.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
));
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));

// Admin makes edit #3 and hides page.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content3-xzx\n",
                'title' => "Test xzx-title3-xzx",
                'security' => "private",
)));
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content3-xzx'));
log_assert(strstr($res['content'], 'xzx-title3-xzx'));

// Dude 1 can't see page.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
log_assert($res['url'] != url_textblock('sandbox/test_page', true));
log_assert(!strstr($res['content'], 'xzx-content3-xzx'));
log_assert(!strstr($res['content'], 'xzx-title3-xzx'));

// Dude 2 is sneaky, tries to diff 1 and 2
// But we are smarter
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_diff('sandbox/test_page', 1, 2, true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
));
log_assert($res['url'] != url_textblock('sandbox/test_page', true));
log_assert(!preg_match('/xzx-content[123]-xzx/', $res['content']));
log_assert(!preg_match('/xzx-title[123]-xzx/', $res['content']));

// Admin makes edit #4 and makes page public again.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content4-xzx\n",
                'title' => "Test xzx-title4-xzx",
                'security' => "public",
            )));
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content4-xzx'));
log_assert(strstr($res['content'], 'xzx-title4-xzx'));

// Looks at history, sees various links
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_history('sandbox/test_page', true),
));
log_assert($res['url'] == url_textblock_history('sandbox/test_page', true));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_diff('sandbox/test_page', 1, 4))));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_restore('sandbox/test_page', 2))));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_revision('sandbox/test_page', 3))));

// Dude 1 diffs 1 and 2, sees various stuff.
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_diff('sandbox/test_page', 1, 2, true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
log_assert($res['url'] == url_textblock_diff('sandbox/test_page', 1, 2, true));
log_assert(strstr($res['content'], 'xzx-content1-xzx'));
log_assert(strstr($res['content'], 'xzx-title1-xzx'));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));

log_print("Textblock tests all passed");
test_cleanup();

?>
