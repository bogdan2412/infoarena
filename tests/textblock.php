#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT_DIR.'www/utilities.php');

test_cleanup();
test_prepare();

log_print("New page with no user, redirect to login.");
$res = curl_test(array(
        'url' => url_textblock('sandbox/test_page'),
));
log_assert_equal($res['redirect_count'],  2);


log_print("New page with some user, redirect to create.");
$res = curl_test(array(
        'url' => url_textblock('sandbox/test_page'),
        'user' => 'test_dude1',
));
log_assert_equal($res['redirect_count'],  1);

log_print("Dude1 creates page.");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_dude1',
        'post' => array(
                'text' => "Test page\nxzx-content1-xzx\n",
                'title' => "Test xzx-title1-xzx",
                'last_revision' => "0",
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(strstr($res['content'], 'xzx-content1-xzx'));
log_assert(strstr($res['content'], 'xzx-title1-xzx'));

usleep(1000000);
log_print("Admin makes edit #2 and protects page.");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "Test page\nxzx-content2-xzx\n",
                'title' => "Test xzx-title2-xzx",
                'security' => "protected",
                'last_revision' => "1",
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));

usleep(1000000);
log_print("Dude1 tries to modify page, doesn't work.");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_dude1',
        'post' => array(
                'text' => "Test page\nxzx-contentF-xzx\n",
                'title' => "Test xzx-titleF-xzx",
                'last_revision' => "2",
)));
log_assert_equal($res['url'],  url_absolute(url_home()));
log_assert(!strstr($res['content'], 'xzx-content2-xzx'));
log_assert(!strstr($res['content'], 'xzx-title2-xzx'));


usleep(1000000);
log_print("Anon sees admin version.");
$res = curl_test(array(
        'url' => url_textblock('sandbox/test_page'),
));
log_assert_equal($res['url'],  url_absolute(
        url_textblock('sandbox/test_page')));
log_assert(strstr($res['content'], 'xzx-content2-xzx'));
log_assert(strstr($res['content'], 'xzx-title2-xzx'));


log_print("Admin makes edit #3 and hides page.");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "Test page\nxzx-content3-xzx\n",
                'title' => "Test xzx-title3-xzx",
                'security' => "private",
                'last_revision' => "2",
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(strstr($res['content'], 'xzx-content3-xzx'));
log_assert(strstr($res['content'], 'xzx-title3-xzx'));

usleep(1000000);
log_print("Dude 1 can't see page.");
$res = curl_test(array(
        'url' => url_textblock('sandbox/test_page'),
        'user' => 'test_dude1',
));
log_assert($res['url'] != url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(!strstr($res['content'], 'xzx-content3-xzx'));
log_assert(!strstr($res['content'], 'xzx-title3-xzx'));


log_print("Dude 2 is sneaky, tries to diff 1 and 2. But we are smarter.");
$res = curl_test(array(
        'url' => url_textblock_diff('sandbox/test_page', 1, 2),
        'user' => 'test_dude2',
));
log_assert($res['url'] != url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(!preg_match('/xzx-content[123]-xzx/', $res['content']));
log_assert(!preg_match('/xzx-title[123]-xzx/', $res['content']));


log_print("Admin makes edit #4 and makes page public again.");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "Test page\nxzx-content4-xzx\n",
                'title' => "Test xzx-title4-xzx",
                'security' => "public",
                'last_revision' => "3",
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(strstr($res['content'], 'xzx-content4-xzx'));
log_assert(strstr($res['content'], 'xzx-title4-xzx'));


log_print("Anon looks at history, sees various links");
$res = curl_test(array(
        'url' => url_textblock_history('sandbox/test_page'),
));
log_assert_equal($res['url'],  url_absolute(
            url_textblock_history('sandbox/test_page')));
log_assert(strstr($res['content'], html_escape(
                url_textblock_diff('sandbox/test_page', 1, 4))));
log_assert(strstr($res['content'], html_escape(
                url_textblock_restore('sandbox/test_page', 2))));
log_assert(strstr($res['content'], html_escape(
                url_textblock_revision('sandbox/test_page', 3))));


log_print("Dude 1 diffs 1 and 2, sees various stuff.");
$res = curl_test(array(
        'url' => url_textblock_diff('sandbox/test_page', 1, 2),
        'user' => 'test_dude1',
));
log_assert_equal($res['url'],  url_absolute(
            url_textblock_diff('sandbox/test_page', 1, 2)));
log_assert(strstr($res['content'], 'xzx-content<del>1</del>-xzx'));
log_assert(strstr($res['content'], 'xzx-title<del>1</del>-xzx'));
log_assert(strstr($res['content'], 'xzx-content<ins>2</ins>-xzx'));
log_assert(strstr($res['content'], 'xzx-title<ins>2</ins>-xzx'));


log_print("Dude 2 tries to move to test_page_2, fail");
$res = curl_test(array(
        'url' => url_textblock_move('/sandbox/tesT_page'),
        'user' => 'test_dude2',
        'post' => array(
            'new_page' => 'sAnDbox//test_page_2',
)));
log_assert_equal($res['redirect_count'],  1);


log_print("Dude 2 tries to delete test_page, redirect to home.");
$res = curl_test(array(
        'url' => url_textblock_delete('sandbox/tesT_page'),
        'user' => 'test_dude2',
        'post' => array(),
));
log_assert_equal($res['redirect_count'],  1);


log_print("Admin looks at move page");
$res = curl_test(array(
        'url' => url_textblock_move('sandbox/tesT_page'),
        'user' => 'test_admin',
));
log_assert_equal($res['redirect_count'],  0);


log_print("Admin moves page");
$res = curl_test(array(
        'url' => url_textblock_move('sandbox/tesT_page'),
        'user' => 'test_admin',
        'post' => array(
            'new_name' => 'sandboX/test_page_2//',
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page_2')));
log_assert_equal($res['redirect_count'],  1);

log_print("DUDE1 looks at test_page_2 history and views everything moved");
$res = curl_test(array(
        'url' => url_textblock_history('sandbox/test_page_2'),
        'user' => 'test_dude1',
));
log_assert_equal($res['url'],  url_absolute(
            url_textblock_history('sandbox/test_page_2', true)));
log_assert(strstr($res['content'], html_escape(
                url_textblock_diff('sandbox/test_page_2', 1, 4))));
log_assert(strstr($res['content'], html_escape(
                url_textblock_restore('sandbox/test_page_2', 2))));
log_assert(strstr($res['content'], html_escape(
                url_textblock_revision('sandbox/test_page_2', 3))));


log_print("Dude2 can try to create test_page again");
$res = curl_test(array(
        'url' => url_textblock('sandbox///tesT_page'),
        'user' => 'test_dude2',
));
log_assert_equal($res['url'],  url_absolute(
            url_textblock_edit('sandbox/test_page', true)));

log_print("Admin creates protected test_page again.");
$res = curl_test(array(
        'url' => url_textblock_edit('sandboX///test_page//'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "Test page\nxzx-private-xzx\n",
                'title' => "Test xzx-private-xzx",
                'security' => "protected",
                'last_revision' => "0",
)));
log_assert_equal($res['url'],  url_absolute(
            url_textblock('sandbox/test_page')));
log_assert(strstr($res['content'], 'xzx-private-xzx'));
log_assert(strstr($res['content'], 'xzx-private-xzx'));

log_print("Dude2 tries to move test_page_2 over test_page, fails.");
$res = curl_test(array(
        'url' => url_textblock_move('sandbox/tesT_page_2'),
        'user' => 'test_dude2',
        'post' => array(
            'new_name' => 'sandbox/test_page',
)));
log_assert($res['url'] != url_absolute(
            url_textblock('sandbox/test_page')));

log_print("Anon sees test_page ok");
$res = curl_test(array(
        'url' => url_textblock('sandboX///test_page//'),
));
log_assert_equal($res['url'], url_absolute(
            url_textblock('sandboX///test_page//')));
log_assert(strstr($res['content'], 'xzx-private-xzx'));
log_assert(strstr($res['content'], 'xzx-private-xzx'));

// FIXME: check some things that should have appeared in the change log.
log_print("Anon looks at changes page and validates html.");
$res = curl_test(array(
        'url' => url_changes(),
));
log_assert_equal($res['redirect_count'],  0);

log_print("Testing security for restore");
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page_revision'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "ugu 1\n",
                'title' => "Test ugu 1",
                'security' => "private",
                'last_revision' => "0",
)));
usleep(1000000);
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page_revision'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "ugu 2\n",
                'title' => "Test ugu 2",
                'security' => "protected",
                'last_revision' => "1",
)));
usleep(1000000);
$res = curl_test(array(
        'url' => url_textblock_edit('sandbox/test_page_revision'),
        'user' => 'test_admin',
        'post' => array(
                'text' => "ugu 3\n",
                'title' => "Test ugu 3",
                'security' => "public",
                'last_revision' => "2",
)));
usleep(1000000);
$res = curl_test(array(
        'url' => url_textblock_restore('sandbox/test_page_revision', 1),
        'user' => 'test_dude1',
        'post' => array()
));
log_assert_equal($res['url'],  url_absolute(url_home()));
log_assert(!strstr($res['content'], 'Nu ai permisiuni'));
$res = curl_test(array(
        'url' => url_textblock_restore('sandbox/test_page_revision', 2),
        'user' => 'test_dude1',
        'post' => array()
));
log_assert_equal($res['url'],  url_absolute(url_home()));
log_assert(!strstr($res['content'], 'Nu ai permisiuni'));
log_print("Textblock tests all passed");
test_cleanup();

?>
