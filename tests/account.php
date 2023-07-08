#! /usr/bin/env php
<?php

require_once(dirname($argv[0]) . "/utilities.php");
require_once(Config::ROOT . "www/utilities.php");

test_cleanup();
test_prepare();

log_print("Anon looks at new account page");
$res = curl_test(array(
        'url' => url_register(),
));
log_assert_equal($res['url'], url_absolute(url_register()));

log_print("Admin looks at new account page, allowed");
$res = curl_test(array(
        'url' => url_register(),
        'user' => 'test_admin',
));
log_assert_equal($res['url'], url_absolute(url_register()));

$test_username = "test_".mt_rand();
$test_password = "pwd".mt_rand();
log_print("Creating $test_username");
$res = curl_test(array(
        'url' => url_register(),
        'post' => array(
                'username' => $test_username,
                'password' => $test_password,
                'password2' => $test_password,
                'full_name' => "xzx-FULL-NAME-xzx",
                'email' => "$test_username@gmail.com",
                'tnc' => 1,
        ),
));
log_assert_equal($res['url'], url_absolute(url_login()));

log_print("Looking at the new user's page");
$res = curl_test(array(
        'url' => url_user_profile($test_username),
        'user' => $test_username,
        'pwd' => $test_password,
));
log_assert_equal($res['redirect_count'], 0);

log_print("Looking at the new user's page stats");
$res = curl_test(array(
        'url' => url_user_stats($test_username),
        'user' => $test_username,
        'pwd' => $test_password,
));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-FULL-NAME-xzx'));

log_print("Looking at the new user's rating page");
$res = curl_test(array(
        'url' => url_user_rating($test_username),
));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'normal'));

log_print("Looking at the new user's stats page");
$res = curl_test(array(
        'url' => url_user_profile($test_username),
));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-FULL-NAME-xzx'));

$test_newpassword = "newpwd".mt_rand();
log_print("User changes his password but is wrong");
$res = curl_test(array(
        'url' => url_account(),
        'user' => $test_username,
        'pwd' => $test_password,
        'post' => array (
                'oldpassword' => $test_password,
                'password' => $test_newpassword,
                'password2' => $test_newpassword + 1,
)));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-FULL-NAME-xzx'));

$test_newpassword = "newpwd".mt_rand();
log_print("User changes his password correctly");
$res = curl_test(array(
        'url' => url_account(),
        'user' => $test_username,
        'pwd' => $test_password,
        'post' => array (
                'passwordold' => $test_password,
                'password' => $test_newpassword,
                'password2' => $test_newpassword,
)));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-FULL-NAME-xzx'));
log_assert(!strstr($res['content'], 'fieldError'));

log_print("User changes his name");
$res = curl_test(array(
        'url' => url_account(),
        'user' => $test_username,
        'pwd' => $test_newpassword,
        'post' => array (
                'full_name' => 'xzx-NEW-FULL-NAME-xzx',
)));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-NEW-FULL-NAME-xzx'));

log_print("Admin looks at user account page");
$res = curl_test(array(
        'url' => url_account($test_username),
        'user' => 'test_admin'
));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-NEW-FULL-NAME-xzx'));

log_print("Admin makes new user into a helper.");
$res = curl_test(array(
        'url' => url_account($test_username),
        'user' => 'test_admin',
        'post' => array(
                'security_level' => 'helper',
)));
log_assert_equal($res['redirect_count'], 0);
log_assert(strstr($res['content'], 'xzx-NEW-FULL-NAME-xzx'));
log_assert(!strstr($res['content'], 'fieldError'));

log_print("User can now see new-task page. Awesome");
$res = curl_test(array(
        'url' => url_task_create(),
        'user' => $test_username,
        'pwd' => $test_newpassword,
));
log_assert_equal($res['redirect_count'], 0);

log_print("User tries to hack himself into an admin");
$res = curl_test(array(
        'url' => url_account(),
        'user' => $test_username,
        'pwd' => $test_newpassword,
        'post' => array(
                'security_level' => 'admin',
)));
log_assert_equal($res['redirect_count'], 1);

log_print("User can't even see security level switched");
$res = curl_test(array(
        'url' => url_account(),
        'user' => $test_username,
        'pwd' => $test_newpassword,
));
log_assert_equal($res['redirect_count'], 0);
log_assert(!stristr($res['content'], 'security_level'));

log_print("Looking at the user page, still a helper");
$res = curl_test(array(
        'url' => url_user_profile($test_username),
        'user' => $test_username,
        'pwd' => $test_password,
));
log_assert_equal($res['redirect_count'], 0);
log_assert(stristr($res['content'], 'helper'));

test_cleanup();
