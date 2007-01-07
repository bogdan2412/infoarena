<?php

require_once(IA_ROOT."common/db/user.php");
require_once(IA_ROOT."common/security.php");

// This module helps access and manage information about the current remote
// user, whether it is a visitor (anonymous) or an authenticated user.
//
// Additionally, you can perform permission queries to check remote
// user's permissions to perform actions against some resources.

// ABOUT AUTHENTICATION
//
// There are 2 entry points for user authentication
//  * identity_from_session()   -- restores identity from cookie based session
//  * identity_from_http()      -- restores identity from HTTP AUTH headers
//
// Start a cookie-based sessions via:   identity_start_session(...)
// End a cookie-based session via:      identity_end_session()

// current remote user, globally accessible
$identity_user = null;

// Returns whether current user is anonymous
function identity_anonymous($identity = null) {
    if (is_null($identity)) {
        global $identity_user;
        return is_null($identity_user);
    }
    else {
        return is_null($identity);
    }
}

// Get user_id for current user.
function identity_get_user_id() {
    log_assert(!identity_anonymous(), 'Check identity_anonymous first');
    global $identity_user;
    return $identity_user['id'];
}

// Check whether current user (or any other arbitrary user) can perform
// a given action (onto an object)
// This is a wrapper for the more-generic, session-independent permission
// module.
function identity_can($action, $object, $identity = null) {
    if (is_null($identity)) {
        global $identity_user;
        $identity = $identity_user;
    }
    return security_query($identity, $action, $object);
}

// Require login first.
// It makes a lot of sense to separate this from security. No matter what
// dumb little security.php might say, some things absolutely require login.
function identity_require_login()
{
    if (identity_anonymous()) {
        flash_error("Mai intai trebuie sa te autentifici.");

        // save current URL. We redirect to here right after logging in
        $_SESSION['_ia_redirect'] = $_SERVER['REQUEST_URI'];
        redirect(url_login());
    }
}

// This function is similar to identity_can(), except that it automatically
// redirects to the login page and displays a generic message when faced
// with insufficient privileges.
//
// Leave $errorMessage set to null and it will automatically display a generic
// error message.
//
// FIXME: message not used, lol.
function identity_require($action, $object = null, $message = null,
                          $identity = null)
{
    $can = identity_can($action, $object, $identity);
    if (!$can) {
        if (identity_anonymous()) {
            // when user is anonymous, send it to login page
            // and redirect it back after login

            flash_error("Mai intai trebuie sa te autentifici.");

            // save current URL. We redirect to here right after logging in
            $_SESSION['_ia_redirect'] = IA_URL_HOST.$_SERVER['REQUEST_URI'];
            redirect(url_login());
        }
        else {
            // User doesn't have enough priviledges, tell him to fuck off.
            flash_error('Nu ai permisiuni suficiente pentru a executa aceasta '
                        .'actiune! Te redirectez ...');
            redirect(url_home());
        }
    }
    return $can;
}

// Initializes long-lived PHP session.
// When remember user is `true`, it will persist session for
// IA_SESSION_LIFETIME_SECONDS seconds.
//
// Here's a good example why PHP sucks.
function init_php_session($remember_user = false) {
    session_name('infoarena_session');
    ini_set('session.gc_maxlifetime', IA_SESSION_LIFETIME_SECONDS);
    if ($remember_user) {
        session_cache_limiter('private');
        session_cache_expire(IA_SESSION_LIFETIME_SECONDS / 60);
        session_set_cookie_params(IA_SESSION_LIFETIME_SECONDS, '/', IA_COOKIE_DOMAIN);
    }
    else {
        session_set_cookie_params(0, '/', IA_COOKIE_DOMAIN);
    }
    session_start();
}

// identity information from cookie-based session
// Returns identity (user) object instance
function identity_from_session() {
    init_php_session();

    if (isset($_SESSION['_ia_identity'])) {
        // log_print('Restoring identity from PHP session');
        $identity = unserialize($_SESSION['_ia_identity']);
    }
    else {
        $identity = null;
    }

    return $identity;
}

// Obtain identity information from HTTP AUTH headers
// Returns identity (user) object instance
function identity_from_http() {
    $user = getattr($_SERVER, 'PHP_AUTH_USER');
    $pass = getattr($_SERVER, 'PHP_AUTH_PW');

    if (!defined("IA_FROM_SMF") && ($user || $pass)) {
        // somebody is trying to authenticate via HTTP
        // log_print('Restoring identity from HTTP AUTH headers');
        $user = user_test_password($user, $pass);

        if (!$user) {
            log_warn("Invalid HTTP AUTH username/password");
        }

        return $user;
    }
    else {
        // nobody is trying to authenticate via HTTP
        return null;
    }
}

// wraps all authentication entry points
function identity_restore() {
    global $identity_user;

    if (!$identity_user) {
        $identity_user = identity_from_session();
    }

    if (!$identity_user) {
        $identity_user = identity_from_http();
    }

    if ($identity_user) {
        log_assert(is_array($identity_user) && getattr($identity_user, 'id'),
                   'Invalid user object found in PHP session store!');
        //log_print('IDENTITY Remote user: '.$identity_user['username']);
        //print_r($_COOKIE);
        //print_r($_SESSION);
    } else {
        //log_print('IDENTITY Anonymous remote user');
    }

    return $identity_user;
}


// Persists $user to session. This is used when logging in.
// When $remember_user is true, it will persist session for
// IA_SESSION_LIFETIME_SECONDS seconds.
function identity_start_session($user, $remember_user = false) {
    session_write_close();
    init_php_session($remember_user);
    $_SESSION['_ia_identity'] = serialize($user);
}

// Update session information. Use this if you change data of current
// remote user.
function identity_update_session($user) {
    $_SESSION['_ia_identity'] = serialize($user);
}

// Terminate session for current user.
function identity_end_session() {
    if (isset($_SESSION['_ia_identity'])) {
        unset($_SESSION['_ia_identity']);
    }
    session_write_close();
    init_php_session();
}

?>
