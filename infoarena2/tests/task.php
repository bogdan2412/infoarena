#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

log_print("Dude1 tries to look at new task page, redirect to login");
$res = curl_test(array(
        'url' => url_task_create(),
        'user' => 'test_dude1'
));
log_assert_equal($res['url'],  url_absolute(url_home()));

log_print("Helper1 looks at new task page, ok");
$res = curl_test(array(
        'url' => url_task_create(),
        'user' => 'test_helper1'
));
log_assert_equal($res['url'],  url_absolute(url_task_create()));

log_print("Helper1 creates a new task, ok");
$res = curl_test(array(
        'url' => url_task_create(),
        'user' => 'test_helper1',
        'post' => array(
                'id' => 'test_task1',
                'type' => 'classic',
)));
log_assert_equal($res['url'], url_absolute(url_task_edit('test_task1')));

// Our edit forms pick up defaults for missing post fields. Helps with testing.
log_print("Helper1 changes task title, author, source, it's ok");
$res = curl_test(array(
        'url' => url_task_edit('test_task1'),
        'user' => 'test_helper1',
        'post' => array(
                'title' => 'xzx-task1-title-xzx',
                'author' => 'xzx-task1-author-xzx',
                'source' => 'xzx-task1-source-xzx',
)));
log_assert_equal($res['url'], url_absolute(url_task_edit('test_task1')));
// Evil
log_assert(!strstr($res['content'], '<span class="fieldError">'));
log_assert(strstr($res['content'], 'xzx-task1-title-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-author-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-source-xzx'));

log_print("Helper2 tries to look at task page, fails(hidden)");
$res = curl_test(array(
        'url' => url_textblock('problema/test_task1'),
        'user' => 'test_helper2',
));
log_assert_equal($res['url'], url_absolute(url_home()));
log_assert(!strstr($res['content'], 'xzx-task1-title-xzx'));
log_assert(!strstr($res['content'], 'xzx-task1-author-xzx'));
log_assert(!strstr($res['content'], 'xzx-task1-source-xzx'));

log_print("Admin looks at task page, ok");
$res = curl_test(array(
        'url' => url_textblock('problema/test_task1'),
        'user' => 'test_admin',
));
log_assert_equal($res['url'], url_absolute(
        url_textblock('problema/test_task1')));
log_assert(strstr($res['content'], 'xzx-task1-title-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-author-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-source-xzx'));

log_print("Helper1 looks at task page, ok");
$res = curl_test(array(
        'url' => url_textblock('problema/test_task1'),
        'user' => 'test_helper1',
));
log_assert_equal($res['url'], url_absolute(
        url_textblock('problema/test_task1')));
log_assert(strstr($res['content'], 'xzx-task1-title-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-author-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-source-xzx'));

log_print("Helper1 tries to make task visible, fails");
$res = curl_test(array(
        'url' => url_task_edit('test_task1'),
        'user' => 'test_helper1',
        'post' => array(
                'hidden' => 0,
)));
log_assert_equal($res['url'], url_absolute(url_home()));

log_print("Dude1 still can't see the task page");
$res = curl_test(array(
        'url' => url_textblock('problema/test_task1'),
        'user' => 'test_dude1',
));
log_assert_equal($res['url'], url_absolute(url_home()));
log_assert(!strstr($res['content'], 'xzx-task1-title-xzx'));
log_assert(!strstr($res['content'], 'xzx-task1-author-xzx'));
log_assert(!strstr($res['content'], 'xzx-task1-source-xzx'));

log_print("Admin makes task visible, ok");
$res = curl_test(array(
        'url' => url_task_edit('test_task1'),
        'user' => 'test_admin',
        'post' => array(
                'hidden' => 0,
)));
log_assert_equal($res['url'], url_absolute(url_task_edit('test_task1')));
log_assert(!strstr($res['content'], '<span class="fieldError">'));

log_print("Anon can now see the task page");
$res = curl_test(array(
        'url' => url_textblock('problema/test_task1'),
        'user' => 'test_dude1',
));
log_assert_equal($res['url'], url_absolute(
            url_textblock('problema/test_task1')));
log_assert(strstr($res['content'], 'xzx-task1-title-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-author-xzx'));
log_assert(strstr($res['content'], 'xzx-task1-source-xzx'));

test_cleanup();
log_print("Task tests all passed");

?>
