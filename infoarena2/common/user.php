<?php

require_once(IA_ROOT."common/db/user.php");

// Password hash function. Must be compatible with SMF.
//
// Also takes into account user name so that users
// sharing the same password can't be detected
function user_hash_password($password, $username) {
    return sha1(strtolower($username).$password);
}

// Computes user unsubscribe key. User must supply this exact key in order
// to unsubscribe from the mailing list.
// $user is user object as returned by user_get_by_username(...)
function user_unsubscribe_key($user) {
    $key = sha1('u:'.$user['username'].':'.$user['password'].':'.IA_SECRET);

    // trim key. make it shorter since long URLs suck in text/plain emails
    return substr($key, 0, 16);
}

// Computes reset password confirmation key.
// User must supply this in order to reset their password.
// $user is user object as returned by user_get_by_username(...)
function user_resetpass_key($user) {
    $key = sha1('r:'.$user['username'].':'.$user['password'].':'.IA_SECRET);

    // trim key. make it shorter since long URLs suck in text/plain emails
    return substr($key, 0, 16);
}

?>
