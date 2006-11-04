<?php
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

// Check whether current user (or any other arbitrary user) can perform
// a given action (onto an object)
// This is a wrapper for the more-generic, session-independent permission
// module.
function identity_can($action, $ontoObject = null, $identity = null) {
    if (is_null($identity)) {
        global $identity_user;
        $identity = $identity_user;
    }

    // Log permission checking.
    // Don't remove this, it's important.
    log_print("Checking permissions".
              " identity=" . ($identity ? $identity['security_level'] : 'anonymous') .
              " action=" . ($action ? (string)$action : 'null') .
              " object=" . ($ontoObject ? (string)$ontoObject : 'null'));

    return permission_query($identity, $action, $ontoObject);
}

// This function is similar to identity_can(), except that it automatically
// redirects to the login page and displays a generic message when faced
// with insufficient privileges.
//
// Leave $errorMessage set to null and it will automatically display a generic
// error message.
function identity_require($action, $ontoObject = null, $errorMessage = null,
                          $identity = null)
{
    $can = identity_can($action, $ontoObject, $identity);
    if (!$can) {
        if (is_null($errorMessage)) {
            $errorMessage = "Nu aveti acces la aceasta resursa!";
        }

        // save current URL. We redirect to here right after logging in
        $_SESSION['_redirect'] = $_SERVER['REQUEST_URI'];

        flash_error($errorMessage);
        redirect(url('login'));
    }

    return $can;
}

// identity information from cookie-based session
// Returns identity (user) object instance
function identity_from_session() {
    session_start();
    if (isset($_SESSION['_identity'])) {
        log_print('Restoring identity from PHP session');
        $identity = unserialize($_SESSION['_identity']);
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

    if ($user || $pass) {
        // somebody is trying to authenticate via HTTP
        log_print('Restoring identity from HTTP AUTH headers');
        $user = user_test_password($user, $pass);

        if (!$user) {
            log_print("Invalid HTTP AUTH username/password");
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
        log_print('Remote user: '.$identity_user['username']);
    }
    else {
        log_print('Anonymous remote user');
    }

    return $identity_user;
}


// Persists $user to session. This is used when logging in.
function identity_start_session($user) {
    $_SESSION['_identity'] = serialize($user);
}

// Terminate session for current user.
function identity_end_session() {
    if (isset($_SESSION['_identity'])) {
        unset($_SESSION['_identity']);
    }
}

?>
