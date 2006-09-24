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

    // Log permission checking.
    // Don't remove this, it's important.
    log_print("Checking permissions".
            " identity=" . ($identity ? $identity['security_level'] : 'anonymous') .
            " action=" . ($action ? (string)$action : 'null') .
            " object=" . ($ontoObject ? (string)$ontoObject : 'null'));

    // valid actions
    $validActions = array('logout', 'login', 'page_index',
                          'edit-profile', 'user-details',
                          'wiki-view', 'wiki-edit', 'wiki-create',
                          'wiki-history', 'wiki-restore',
                          'history', 'textblock-listattach',
                          'textblock-attach', 'attach-overwrite',
                          'attach-download', 'attach-delete',
                          'task-view', 'task-submit', 'task-edit',
                          'task-history', 'task-restore',
                          'task-create', 'task-publish',
                          'news-view', 'news-edit', 'news-create',
                          'news-history', 'news-restore', 
                          'round-view', 'round-create', 'round-edit',
                          'round-submit', 'round-history', 'round-restore',
                          'macro-debug');
    ia_assert(false !== array_search($action, $validActions),
              'Invalid permission: "' . $action . '"');

    // first, handle anonymous users
    if (is_null($identity)) {
        switch($action) {
            case 'login':
            case 'attach-download':
            case 'wiki-listattach':
            case 'wiki-view':
            case 'news-view':
            case 'round-view':
            case 'wiki-history':
            case 'news-history':
            case 'round-history':
                return true;

            case 'task-view':
                return $ontoObject && !$ontoObject['hidden'];

            default:
                return false;
        }
    }
    
    // second, handle administrators. admins can do everything
    if ('admin' == $identity['security_level']) {
        return true;
    }

    // we (temporarily) implement a very basic security model
    // this is an O(M*N) decision matrix; it's bound to be ugly
    $level = $identity['security_level'];
    $objOwner = getattr($ontoObject, 'user_id', null);
    switch ($action) {
        case 'login':
        case 'logout':
        case 'wiki-view':
        case 'wiki-history':
        case 'attach-download':
        case 'wiki-listattach':
        case 'news-view':
        case 'round-view':
        case 'wiki-history':
        case 'news-history':
        case 'round-history':
            return true;

        case 'task-view':
        case 'task-submit':
        case 'task-history':
            // hidden tasks are only visible to reviewers, admin
            // or their owners
            switch ($level) {
                case 'reviewer':
                    return true;
                default:
                    return $ontoObject && (!$ontoObject['hidden']
                           || $identity['id'] == $objOwner);
            }

        case 'round-submit':
            // users can submit solutions to problem inside a round, only
            // if that round is active or if they are editors/admins
            switch ($level) {
                case 'reviewer':
                    return true;
                default:
                    return $ontoObject && ('1' == $ontoObject['active']);
            }

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
        case 'wiki-restore':
        case 'task-restore':
        case 'attach-delete':
        case 'attach-overwrite':
        case 'wiki-restore':
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
        case 'news-restore':
        case 'round-create':
        case 'round-edit':
        case 'round-restore':
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

