#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(IA_ROOT.'www/utilities.php');
require_once(IA_ROOT.'common/db/db.php');

test_cleanup();
test_prepare();

log_print("WARNING: This test requires the evaluator.");

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


log_print("Admin looks at round page, sees links to tasks");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
        'user' => 'test_admin',
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/adunare')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));


log_print("Admin removes task adunare, adds flip and biti.");
$res = curl_test(array(
        'url' => url_round_edit('test_round'),
        'user' => 'test_admin',
        'post' => array(
                'tasks' => array('cmmdc', 'flip', 'biti'),
)));
log_assert_equal($res['url'], url_absolute(url_round_edit('test_round')));
log_assert(!strstr($res['content'], '<span class="fieldError"'));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));


log_print("Admin looks at round page again, adunare is gone");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
        'user' => 'test_admin',
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(!strstr($res['content'], '<a href="'.
            url_textblock('problema/adunare')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/flip')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/biti')));


log_print("Anon looks at round page, sees that round hasn't started");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(strstr($res['content'], '<div class="round status waiting">'));
log_assert(!strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));
log_assert(!strstr($res['content'], '<a href="'.
            url_textblock('problema/flip')));
log_assert(!strstr($res['content'], '<a href="'.
            url_textblock('problema/biti')));


log_print("Admin makes round an archive starting 3 seconds in the future");
$start_date = db_date_format(time() + 3);
$res = curl_test(array(
        'url' => url_round_edit('test_round'),
        'user' => 'test_admin',
        'post' => array(
                'type' => 'archive',
                'start_time' => $start_date,
        ),
));
log_assert_equal($res['url'], url_absolute(url_round_edit('test_round')));
log_assert(!strstr($res['content'], '<span class="fieldError"'));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(strstr($res['content'], $start_date));


log_print("Admin looks at round page, round still not started");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
        'user' => 'test_admin',
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(strstr($res['content'], '<div class="round status waiting">'));
log_assert(!strstr($res['content'], '<div class="round status running">'));
log_assert(!strstr($res['content'], '<div class="round status complete">'));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/flip')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/biti')));


// Yuck
log_print("Waiting for 3 seconds...");
usleep(3 * 1000000);


log_print("Anon looks at round page, sees that round started");
$res = curl_test(array(
        'url' => url_textblock("runda/test_round"),
));
log_assert_equal($res['url'], url_absolute(url_textblock('runda/test_round')));
log_assert(strstr($res['content'], 'xzx-round-title-xzx'));
log_assert(!strstr($res['content'], '<div class="round status waiting">'));
log_assert(strstr($res['content'], '<div class="round status running">'));
log_assert(!strstr($res['content'], '<div class="round status complete">'));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/cmmdc')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/flip')));
log_assert(strstr($res['content'], '<a href="'.
            url_textblock('problema/biti')));


log_print("Basic round passed");
//test_cleanup();

?>
