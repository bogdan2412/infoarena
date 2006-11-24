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
        return security_task($user, $action, $task);
    }

    // Forward security to round.
    if (preg_match("/^ \s* round: \s* ([a-z0-9]*) \s* $/xi", $textsec, $matches)) {
        $round = round_get($matches[1]);
        if ($round === null) {
            log_warn("Bad security descriptor, ask an admin.");
            return $usersec == 'admin';
        }
        return security_round($user, $action, $round);
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
        case 'textblock-list-attach':
        case 'attach-download':
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
                return $usersec != 'anonymous';
            } else {
                return $usersec == 'admin';
            }

        // Permanent changes. Admin only
        case 'textblock-move':
        case 'textblock-delete':
        case 'textblock-attach':
        case 'attach-overwrite':
        case 'attach-delete':
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
            // FIXME?: we could use this for banning IPs
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
    if (preg_match('/$grader_/', $attach['name'])) {
        $action = preg_replace('/$attach/', 'grader', $action);
    }
    return security_textblock($user, $action, textblock_get_revision($attach['page']));
}

// FIXME: round logic.
function security_task($user, $action, $task) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    // Normalize action.
    switch ($action) {
        // Read-only access.
        case 'textblock-view':
        case 'textblock-history':
        case 'textblock-list-attach':
        case 'task-view':
        case 'attach-download':
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
        case 'textblock-attach':
        case 'attach-overwrite':
        case 'attach-delete':
        case 'grader-download':
        case 'grader-create':
        case 'grader-overwrite':
        case 'grader-delete':
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
        case 'textblock-list-attach':
        case 'attach-download':
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
        case 'textblock-attach':
        case 'attach-overwrite':
        case 'attach-delete':
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
        case 'macro-remotebox':
            // only administrators can execute these macros 
            return $usersec == 'admin';

        default:
            log_error('Invalid macro action: '.$action);
            return false;
    }
}

// FIXME: implement job security.
// * job-view-source
// * job-view
//
// There is no job-eval, jobs are evaluated on the spot, we check job-view instead.
function security_job($user, $action, $job) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        default:
            log_error('Invalid job action: '.$action);
            return false;
    }
}

?>
