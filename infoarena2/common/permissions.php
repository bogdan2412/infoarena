<?php
// This module implements permission policy (regulates who can do what on the website).
//
// You tipically don't interact with the functions below but use identity_require() and identity_can()
// from `identity.php` to enforce proper permissions.

// We distinguish between 4 types of users:
//  - anonymous     non-authenticated visitors
//  - normal        registered & authenticated users
//  - reviewer      users who are allowed to contribute with content & new (hidden) tasks but are not
//                  trusted enough to access administrative panels.
//  - admin         can do anything, including starting/stopping contests and viewing any hidden task/contest





// Returns boolean whether $user can perform $action $ontoObject.
//
//      $user           is instance of user model as returned by user_get*()
//      $action         convention. see source code for valid actions
//      $ontoObject     hacky, magically un-typed, usually a model instance as returned by db.php functions.
//                      However, some actions may expect it to be null or any arbitrary type.
function permission_query($user, $action, $ontoObject) {
    // split actions (wiki-view) into an action group (wiki) and action (view)
    list($group, $action) = split("-", $action);

    // group dispatcher
    switch ($group) {
        case 'user':
            return permission_user($user, $action, $ontoObject);

        case 'wiki':
        case 'news':
            return permission_wiki($user, $action, $ontoObject);

        case 'round':
            return permission_round($user, $action, $ontoObject);

        case 'task':
            return permission_task($user, $action, $ontoObject);

        case 'attach':
            return permission_attach($user, $action, $ontoObject);

        case 'macro':
            return permission_macro($user, $action, $ontoObject);

        case 'job':
            return permission_job($user, $action, $ontoObject);

        case 'specialpage':
            return permission_specialpage($user, $action, $ontoObject);

        default:
            log_error('Invalid action group: "' . $group . '"');
            return false;
    }
}

function permission_user($user, $action, $ontoUser) {
    switch ($action) {
        case 'login':
            // any user can try to login, even if it's already authenticated
            return true;

        case 'logout':
            // only authenticated users can logout
            return !is_null($user);

        case 'viewprofile':
            // anyone can view anyone's profile
            return true;

        case 'editprofile':
            // anyone can edit their own profile. admins can edit any profile
            return $user && $ontoUser && ($user['id'] == $ontoUser['id'] || 'admin' == $user['security_level']);

        case 'view':
        case 'history':
            // anyone can view user profile pages
            return true;

        case 'edit':
        case 'restore':
            // users can edit their own profile pages. admins can edit any user profile page
            return $user && $ontoUser && ($user['id'] == $ontoUser['id'] || 'admin' == $user['security_level']);

        case 'create':
            // there should be no need to create user pages since a user profile page is automatically created
            // upon registration
            return false;

        default:
            log_error('Invalid user action: '.$action);
            return false;
    }
}

function permission_wiki($user, $action, $textblock) {
    switch ($action) {
        case 'view':
        case 'history':
            // anyone can view a wiki page & its history
            return true;

        case 'edit':
        case 'create':
        case 'restore':
            // admins & reviewers can edit/create/restore wiki pages
            return ('admin' == $user['security_level'] || 'reviewer' == $user['security_level']);

        default:
            log_error('Invalid wiki action: '.$action);
            return false;
    }
}

function permission_attach($user, $action, $attach) {
    switch ($action) {
        case 'download':
        case 'list':
            // anyone can download/list attachments
            return true;

        case 'create':
        case 'overwrite':
            // only reviewers/admins can create/replace attachments
            return ('admin' == $user['security_level'] || 'reviewer' == $user['security_level']);

        case 'delete':
            // Only admins can delete attachments for good.
            // Since deleted attachments are gone for good, we allow this action only to administrators
            // in order to control/harden an eventual damage caused by a reviewer "gone bad".
            return $attach && ('admin' == $user['security_level']);

        default:
            log_error('Invalid attach action: '.$action);
            return false;
    }
}

function permission_task($user, $action, $task) {
    $level = getattr($user, 'security_level');

    switch ($action) {
        case 'view':
        case 'history':
            // anyone can view public tasks; reviewers can also view their own hidden tasks; admins view everything
            return $task && (!$task['hidden'] || 'admin'==$level || ($user && 'reviewer'==$level && $task['user_id']==$user['id']));

        case 'submit':
            // any authenticated user can submit to public tasks;
            // reviewers can also submit to their own hidden tasks;
            // admins submit to everything
            if (!$user) {
                // no anonymous submissions, please
                return false;
            }
            return $task && (!$task['hidden'] || 'admin'==$level || ('reviewer'==$level && $task['user_id']==$user['id']));

        case 'edit':
        case 'restore':
            // reviewers edit their own tasks; admins edit everything
            return $user && $task && ('admin'==$level || ('reviewer'==$level && $task['user_id']==$user['id']));

        case 'create':
            // reviewers & admins can create tasks
            return $user && ('admin'==$level || 'reviewer'==$level);

        case 'publish':
            // only admins can publish tasks (un-hide them)
            return $user && ('admin'==$level);

        default:
            log_error('Invalid task action: '.$action);
            return false;
    }
}

function permission_round($user, $action, $round) {
    $level = getattr($user, 'security_level');

    switch ($action) {
        case 'view':
        case 'history':
            // anyone can view a round (contest) page and its history
            return true;

        case 'edit':
        case 'create':
        case 'restore':
            // only administrators edit/create round pages
            return $user && ('admin'==$level);

        default:
            log_error('Invalid task action: '.$action);
            return false;
    }
}

function permission_specialpage($user, $action, $textblock) {
    $level = getattr($user, 'security_level');

    switch ($action) {
        case 'index':
            // only admins & reviewers can view page index
            // security precaution
            return $user && ('admin'==$level || 'reviewer'==$level);

        default:
            log_error('Invalid special page action: '.$action);
            return false;
    }
}

function permission_macro($user, $action, $args) {
    $level = getattr($user, 'security_level');

    switch ($action) {
        case 'debug':
            // only administrators & reviewers can execute a debugging macro
            return $user && ('admin'==$level || 'reviewer'==$level);
        
        default:
            log_error('Invalid macro action: '.$action);
            return false;
    }
}

function permission_job($user, $action, $job) {
    $level = getattr($user, 'security_level');

    switch ($action) {
        case 'eval':
            if ('admin' == $level || 'reviewer' == $level) {
                return true;
            }
            // FIXME FIXME FIXME: retarded hack here.
            // FIXME: proper implementation.
            log_warn("job-eval is RANDOM!!!");
            return rand(0, 100) < 50;
        default:
            log_error('Invalid macro action: '.$action);
            return false;
    }
}

?>
