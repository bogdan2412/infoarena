#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

log_print("Helper1 looks at new round page, fails");
$res = curl_test(array(
        'url' => url_round_create(),
        'user' => 'test_helper1',
));
log_assert($res['url'] != url_absolute(url_round_create()));

log_print("Helper1 tries to create a new round, fails");
$res = curl_test(array(
        'url' => url_round_create(),
        'user' => 'test_helper1',
        'post' => array(
                'id' => 'test_round',
)));
log_assert_equal($res['url'], url_absolute(url_home()));

log_print("Helper2 looks at round page, but it's not there");
$res = curl_test(array(
        'url' => url_textblock('runda/test_round'),
        'user' => 'test_helper2',
));
log_assert_equal($res['url'], url_absolute(url_textblock_edit('runda/test_round')));

log_print("Admin looks at new round page, ok");
$res = curl_test(array(
        'url' => url_round_create(),
        'user' => 'test_admin',
));
log_assert_equal($res['url'], url_absolute(url_round_create()));

log_print("Admin creates round.");
$res = curl_test(array(
        'url' => url_round_create(),
        'user' => 'test_admin',
        'post' => array(
                'id' => 'test_round',
)));
log_assert_equal($res['url'], url_absolute(url_round_edit('test_round')));
log_assert(strstr($res['content'], 'test_round'));

log_print("Admin adds tasks adunare and cmmdc to round.");
$res = curl_test(array(
        'url' => url_round_edit('test_round'),
        'user' => 'test_admin',
        'post' => array(
                'title' => 'xzx-round-title-xzx',
                'tasks' => array('adunare', 'cmmdc'),
)));
log_assert_equal($res['url'], url_absolute(url_round_edit('test_round')));
log_assert(!strstr($res['content'], '<span class="fieldError"'));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));

log_print("Anon looks at round page, sees links to tasks");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/adunare')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));

log_print("Admin removes task adunare.");
$res = curl_test(array(
        'url' => url_round_edit('test_round'),
        'user' => 'test_admin',
        'post' => array(
                'tasks' => array('cmmdc'),
)));
log_assert_equal($res['url'], url_absolute(url_round_edit('test_round')));
log_assert(!strstr($res['content'], '<span class="fieldError"'));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));

log_print("Anon looks at round page again, adunare is gone");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(!strstr($res['content'], '<a href="'.
            url_textblock('problema/adunare')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));

log_print("Basic round editting tests passed");
test_cleanup();

?>
