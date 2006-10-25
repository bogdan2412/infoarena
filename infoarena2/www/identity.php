<?php
// This module helps access and manage information about the current remote user,
// whether it is a visitor (anonymous) or an authenticated user.
//
// Additionally, you can perform permission queries to check remote user's permissions
// to perform actions against some resources.



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

// Check whether current user (or any other arbitrary user) can perform a given action (onto an object)
// This is a wrapper for the more-generic, session-independent permission module.
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

// Try and restore identity information from session
function identity_restore() {
    global $identity_user;

    session_start();
    if (isset($_SESSION['_identity'])) {
        $identity_user = unserialize($_SESSION['_identity']);
    }
    else {
        $identity_user = null;
    }
}

