<?php
// This module implements everything related to security.
//
// You should use security_query to determine if a certain user is allowed
// to do a an operation. The operation is a large php array(hash), which
// should completely describe the operation.
//
// NOTE: most of the time you can use identity_can or identity_require
// instead of calling security_query directly.

// Implementation:
//
// We distinguish between 5 types of users. Permissions only increase as you
// go down the list.
//  - anonymous     non-authenticated visitors.
//  - normal        registered & authenticated users.
//  - helper        Trusted users. They can make their own tasks/rounds, but
//                  can't publish them. For teachers or high ratings.
//  - admin         Can do anything. For core team members.

// Returns boolean whether $user can perform $action onto $object
function security_query($user, $action, $object) {
    list($group, $subaction) = explode('-', $action, 2);

    // Log security checking.
    $username = getattr($user, 'username', 'null');
    $usersec = getattr($user, 'security_level', 'anonymous');
    $object_id = getattr($object, 'id', getattr($object, 'name', $object));
    log_print("SECURITY QUERY ".
            "($username, $usersec, $action, $object_id): ".
            "username, level, action, object");

    // group dispatcher
    switch ($group) {
        case 'textblock':
            return security_textblock($user, $action, $object);

        case 'user':
            return security_user($user, $action, $object);

        case 'round':
            return security_round($user, $action, $object);

        case 'task':
            return security_task($user, $action, $object);

        case 'attach':
            return security_attach($user, $action, $object);

        case 'macro':
            return security_macro($user, $action, $object);

        case 'job':
            return security_job($user, $action, $object);

        default:
            log_error('Invalid group: "' . $group . '"');
            return false;
    }
}

// Handles textblock security.
function security_textblock($user, $action, $textblock) {
    $textsec = $textblock['security'];
    $usersec = getattr($user, 'security_level', 'anonymous');

    // Forward security to task.
    if (preg_match("/^ \s* task: \s* ([a-z0-9]*) \s* $/xi", $textsec, $matches)) {
        $task = task_get($matches[1]);
        if ($task === null) {
            log_warn("Bad security descriptor, ask an admin.");
            return $usersec == 'admin';
        }
        return security_query_task($user, $action, $task);
    }
    if (preg_match('/^ \s* (private|protected|public) \s* $/xi', $textsec, $matches)) {
        $textsec = $matches[1];
    } else {
        log_warn("Bad security descriptor, ask an admin.");
        return $usersec == 'admin';
    }

    switch ($action) {
        // Read-only
        case 'textblock-view':
        case 'textblock-history':
            if ($textsec == 'private') {
                return $usersec == 'admin';
            } else {
                return true;
            }

        // Reversible modifications.
        case 'textblock-edit':
        case 'textblock-create': 
        case 'textblock-restore':
            if ($textsec == 'public') {
                return true;
            } else {
                return $usersec == 'admin';
            }

        // Permanent changes. Admin only
        case 'textblock-move':
        case 'textblock-delete':
            return $usersec == 'admin';

        // Special: admin only.
        case 'textblock-change-security':
            return $usersec == 'admin';

        default:
            log_error('Invalid wiki action: '.$action);
            return false;
    }
}

function security_user($user, $action, $target_user) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        case 'user-login':
            // FIXME: should we really handle this in security code?
            // any user can try to login, even if it's already authenticated
            return true;

        case 'user-logout':
            // FIXME: should we really handle this in security code?
            // only authenticated users can logout
            return !is_null($user);

        case 'user-viewinfo':
            // This is for userinfo/$userinfo/*
            // user-info is public.
            return true;

        case 'user-editprofile':
            // anyone can edit their own profile. admins can edit any profile
            return ($user['id'] == $target_user['id'] || $usersec == 'admin');

        default:
            log_error('Invalid user action: '.$action);
            return false;
    }
}

// FIXME: query textblock here.
// FIXME: magic prefix.
function security_attach($user, $action, $attach) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        case 'attach-download':
        case 'attach-list':
            // anyone can download/list attachments
            return true;

        // Irreversible modifications, admin only:
        case 'attach-create':
        case 'attach-overwrite':
        case 'attach-delete':
            return $usersec == 'admin';

        default:
            log_error('Invalid attach action: '.$action);
            return false;
    }
}

// FIXME: round logic.
function security_task($user, $action, $task) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    // Normalize action.
    switch ($action) {
        // Read-only access.
        case 'task-view':
        case 'textblock-view':
        case 'textblock-history':
            // Everybody can see public tasks.
            // Helpers can see their own tasks.
            return (!$task['hidden']) || ($usersec == 'admin') ||
                    ($task['hidden'] && $usersec == 'helper' && $task['user_id'] == $user['id']) ||
                    ($usersec == 'admin');

        // Edit access.
        case 'task-edit':
        case 'textblock-edit':
        case 'textblock-restore':
        case 'textblock-move':
            return ($task['hidden'] && $usersec == 'helper' && $task['user_id'] == $user['id']) ||
                    $usersec == 'admin';

        // Admin stuff:
        case 'task-hide':
        case 'task-publish':
        case 'textblock-delete':
        case 'textblock-change-security':
            return $usersec == 'admin';

        // Special: submit.
        case 'task-submit':
            if ($usersec == 'anonymous') {
                return false;
            }
            if ($task['hidden']) {
                return $usersec == 'admin' || ($usersec == 'helper' && $task['user_id'] = $user['id']);
            } else {
                return true;
            }

        // Special, creating a new task.
        case 'task-create':
            return $usersec == 'admin' || $usersec == 'helper';

        default:
            log_error('Invalid task action: '.$action);
            return false;
    }
}

// FIXME: contest logic.
function security_round($user, $action, $round) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        // Read-only access.
        case 'round-view':
        case 'textblock-view':
        case 'textblock-history':
            // Everybody can see public rounds.
            // Helpers can see their own rounds.
            return (!$round['hidden']) || ($usersec == 'admin') ||
                    ($round['hidden'] && $usersec == 'helper' && $round['user_id'] == $user['id']) ||
                    ($usersec == 'admin');

        // Edit access.
        case 'round-edit':
        case 'textblock-edit':
        case 'textblock-restore':
        case 'textblock-move':
            return ($round['hidden'] && $usersec == 'helper' && $round['user_id'] == $user['id']) ||
                    $usersec == 'admin';

        // Admin only operations:
        case 'textblock-change-security':
        case 'textblock-delete':
        case 'round-publish':
        case 'round-hide':
            return $usersec == 'admin';

        // Special, creating a new round.
        case 'round-create':
            return $usersec == 'admin' || $usersec == 'helper';

        default:
            log_error('Invalid round action: '.$action);
            return false;
    }
}

function security_macro($user, $action, $args) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        case 'macro-debug':
        case 'macro-grep':
            // only administrators can execute these macros 
            return $usersec == 'admin';
        
        default:
            log_error('Invalid macro action: '.$action);
            return false;
    }
}

function security_job($user, $action, $job) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        case 'job-eval':
            // FIXME: proper implementation.
            log_warn("job-eval is RANDOM!!!");
            return rand(0, 100) < 50;
        default:
            log_error('Invalid job action: '.$action);
            return false;
    }
}

?>
