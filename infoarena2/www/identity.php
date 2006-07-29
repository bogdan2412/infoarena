<?php
/**
 * This module helps access and manage information about the current
 * remote user, whether it is a visitor or a logged-in user.
 *
 * You tipically use this to know whether someone is logged in, who is it
 * but also to check its permissions against some resources.
 *
 * Note: These functions use the global `$identity_user` as the security
 * subject. However, you can explicitly check permissions for arbitrary
 * users. Just have a look at the function definitions.
 */

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

// Check whether current user can perform a given action (onto an object)
function identity_can($action, $ontoObject = null, $identity = null) {
    if (is_null($identity)) {
        global $identity_user;
        $identity = $identity_user;
    }

    // valid actions
    $validActions = array('logout', 'login',
                          'wiki-view', 'wiki-edit', 'wiki-create',
                          'wiki-listattach',
                          'wiki-attach', 'attach-overwrite',
                          'attach-download', 'attach-delete',
                          'task-view', 'task-submit', 'task-edit',
                          'task-create', 'task-publish',
                          'edit-profile', 'user-details',
                          'news-view', 'news-edit', 'news-create');
    assert(false !== array_search($action, $validActions));

    // first, handle anonymous users
    if (is_null($identity)) {
        switch($action) {
            case 'login':
            case 'wiki-view':
            case 'attach-download':
            case 'wiki-listattach':
            case 'task-view':
            case 'news-view':
                return true;

            default:
                return false;
        }
    }
    
    // second, handle administrators. admins can do everything
    if ('admin' == $identity['security_level']) {
        return true;
    }

    // we (temporarily) implement a very basic security model
    // this is an O(M*N) decision matrix so it is ugly
    $level = $identity['security_level'];
    $objOwner = getattr($ontoObject, 'user_id', null);
    switch ($action) {
        case 'login':
        case 'logout':
        case 'wiki-view':
        case 'attach-download':
        case 'wiki-listattach':
        case 'task-view':
        case 'news-view':
        case 'task-submit':
            return true;

        case 'wiki-create':
        case 'wiki-attach':
        case 'task-create':
            switch ($level) {
                case 'reviewer':
                case 'editor':
                    return true;
            }
            return false;

        case 'wiki-edit':
        case 'task-edit':
        case 'attach-delete':
        case 'attach-overwrite':
            switch ($level) {
                case 'reviewer':
                    return true;
                case 'editor':
                    return $identity['id'] == $objOwner;
            }
            return false;

        case 'task-publish':
        case 'news-create':
        case 'news-edit':
            switch ($level) {
                case 'reviewer':
                    return true;
            }
            return false;
            
        case 'edit-profile':
            return true;
     
    }

    return false;
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

