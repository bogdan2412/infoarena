#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();


log_print("New page with no user, redirect to login.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
));
validate_html($res['content']);
log_assert($res['redirect_count'] == 2);


log_print("New page with some user, redirect to create.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
validate_html($res['content']);
log_assert($res['redirect_count'] == 1);


log_print("Dude1 creates page.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content1-xzx\n",
                'title' => "Test xzx-title1-xzx",
)));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content1-xzx'));
log_assert(strstr($res['content'], 'xzx-title1-xzx'));


log_print("Admin makes edit #2 and protects page.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content2-xzx\n",
                'title' => "Test xzx-title2-xzx",
                'security' => "protected",
)));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));


log_print("Dude1 tries to modify page, doesn't work.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-contentF-xzx\n",
                'title' => "Test xzx-titleF-xzx",
)));
validate_html($res['content']);
log_assert($res['url'] != url_textblock(true));
log_assert(!strstr($res['content'], 'xzx-content2-xzx'));
log_assert(!strstr($res['content'], 'xzx-title2-xzx'));


log_print("Anon sees admin version.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));


log_print("Admin makes edit #3 and hides page.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content3-xzx\n",
                'title' => "Test xzx-title3-xzx",
                'security' => "private",
)));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content3-xzx'));
log_assert(strstr($res['content'], 'xzx-title3-xzx'));


log_print("Dude 1 can't see page.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
validate_html($res['content']);
log_assert($res['url'] != url_textblock('sandbox/test_page', true));
log_assert(!strstr($res['content'], 'xzx-content3-xzx'));
log_assert(!strstr($res['content'], 'xzx-title3-xzx'));


log_print("Dude 2 is sneaky, tries to diff 1 and 2. But we are smarter.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_diff('sandbox/test_page', 1, 2, true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
));
validate_html($res['content']);
log_assert($res['url'] != url_textblock('sandbox/test_page', true));
log_assert(!preg_match('/xzx-content[123]-xzx/', $res['content']));
log_assert(!preg_match('/xzx-title[123]-xzx/', $res['content']));


log_print("Admin makes edit #4 and makes page public again.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_edit('sandbox/test_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => array(
                'text' => "Test page\nxzx-content4-xzx\n",
                'title' => "Test xzx-title4-xzx",
                'security' => "public",
)));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('sandbox/test_page', true));
log_assert(strstr($res['content'], 'xzx-content4-xzx'));
log_assert(strstr($res['content'], 'xzx-title4-xzx'));


log_print("Anon looks at history, sees various links");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_history('sandbox/test_page', true),
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock_history('sandbox/test_page', true));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_diff('sandbox/test_page', 1, 4))));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_restore('sandbox/test_page', 2))));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_revision('sandbox/test_page', 3))));


log_print("Dude 1 diffs 1 and 2, sees various stuff.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_diff('sandbox/test_page', 1, 2, true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock_diff('sandbox/test_page', 1, 2, true));
log_assert(strstr($res['content'], 'xzx-content1-xzx'));
log_assert(strstr($res['content'], 'xzx-title1-xzx'));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));


log_print("Dude 2 tries to move to test_page_2, fail");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_move('/sandbox/tesT_page', true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
        CURLOPT_POSTFIELDS => array(
            'new_page' => 'sAnDbox//test_page_2',
)));
validate_html($res['content']);
log_assert($res['redirect_count'] == 1);


log_print("Dude 2 tries to delete test_page, redirect to home.");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_delete('sandbox/tesT_page', true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
        CURLOPT_POST => true,
));
validate_html($res['content']);
log_assert($res['redirect_count'] == 1);


log_print("Admin looks at move page");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_move('sandbox/tesT_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
));
validate_html($res['content']);
log_assert($res['redirect_count'] == 0);


log_print("Admin moves page");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_move('sandbox/tesT_page', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
        CURLOPT_POSTFIELDS => array(
            'new_name' => 'sandboX/test_page_2//',
)));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('sandbox/test_page_2', true));
log_assert($res['redirect_count'] == 1);


log_print("DUDE1 looks at test_page_2 history and views everything moved");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock_history('sandbox/test_page_2', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock_history('sandbox/test_page_2', true));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_diff('sandbox/test_page_2', 1, 4))));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_restore('sandbox/test_page_2', 2))));
log_assert(strstr($res['content'], htmlentities(
                url_textblock_revision('sandbox/test_page_2', 3))));


log_print("Dude2 can create test_page again");
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('sandbox///tesT_page', true),
        CURLOPT_USERPWD => 'test_dude2:pwd',
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock_edit('sandbox/test_page', true));


// FIXME: check some things that should have appeared in the change log.
log_print("Anon looks at changes page and validates html.");
$res = quick_curl(array(
        CURLOPT_URL => url('changes', array(), true),
));
validate_html($res['content']);
log_assert($res['redirect_count'] == 0);


log_print("Textblock tests all passed");
test_cleanup();

?>
