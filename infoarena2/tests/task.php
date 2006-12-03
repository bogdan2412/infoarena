#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once('www/utilities.php');

test_cleanup();
test_prepare();

// View problema/adunare
$res = quick_curl(array(
        CURLOPT_URL => url_textblock('problema/adunare', true),
));
validate_html($res['content']);
log_assert($res['url'] == url_textblock('problema/adunare', true),
        "View problema/adunare");

// Download tests as anon user, fails
$res = quick_curl(array(
        CURLOPT_URL => url_attachment('problema/adunare', 'grader_eval.c', true),
));
log_assert($res['http_code'] == 404,
        "Try to download tests as anon");

// Download tests as random user, fails
$res = quick_curl(array(
        CURLOPT_URL => url_attachment('problema/adunare', 'grader_eval.c', true),
        CURLOPT_USERPWD => 'test_dude1:pwd',
));
log_assert($res['http_code'] == 404,
        "Try to download tests as dude");

// Download tests as random user, fails
$res = quick_curl(array(
        CURLOPT_URL => url_attachment('problema/adunare', 'grader_eval.c', true),
        CURLOPT_USERPWD => 'test_admin:pwd',
));
log_assert(strstr($res['content'], '#include <stdio.h>'),
        'Try to download tests as admin');

log_print("Task tests all passed");

test_cleanup();
?>
