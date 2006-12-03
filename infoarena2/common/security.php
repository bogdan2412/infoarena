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
    log_print("SECURITY QUERY: ".
            "($username, $usersec, $action, $object_id): ".
            "(username, level, action, object)");

    // group dispatcher
    switch ($group) {
        case 'textblock':
            $result = security_textblock($user, $action, $object);
            break;

        case 'user':
            $result = security_user($user, $action, $object);
            break;

        case 'round':
            $result = security_round($user, $action, $object);
            break;

        case 'task':
            $result = security_task($user, $action, $object);
            break;

        case 'attach':
            $result = security_attach($user, $action, $object);
            break;

        case 'macro':
            $result = security_macro($user, $action, $object);
            break;

        case 'job':
            $result = security_job($user, $action, $object);
            break;

        default:
            log_error('Invalid action group: "' . $group . '"');
    }

    log_assert(is_bool($result), "SECURITY: FAILED, didn't return a bool");
    if ($result) {
        log_print("SECURITY: GRANTED");
    } else {
        log_print("SECURITY: DENIED");
    }
    return $result;
}

// This function simplifies $action.
function security_simplify_action($action)
{
    switch ($action) {
        // View access.
        case 'textblock-view':
        case 'textblock-history':
        case 'textblock-list-attach':
        case 'attach-download':
        case 'user-viewinfo':
        case 'task-view':
        case 'round-view':
            return 'simple-view';

        // Reversible edits access.
        case 'textblock-edit':
        case 'textblock-restore':
        case 'textblock-attach':
        case 'textblock-create':
            return 'simple-rev-edit';

        // Irreversible edits.
        case 'textblock-move':
        case 'attach-overwrite':
        case 'attach-delete':
        case 'task-edit':
        case 'task-create':
        case 'task-delete':
        case 'round-edit':
        case 'round-create':
        case 'round-delete':
        case 'textblock-delete':
        case 'grader-download':
        case 'grader-overwrite':
        case 'grader-delete':
            return 'simple-edit';

        // Admin stuff:
        case 'task-hide':
        case 'task-publish':
        case 'round-hide':
        case 'round-publish':
        case 'textblock-change-security':
            return 'simple-critical';

        // Special actions fall through
        // FIXME: As few as possible.
        case 'task-submit':
        case 'user-editprofile':
            return $action;

        default:
            log_error('Invalid action: '.$action);
    }
}

// Handles textblock security.
function security_textblock($user, $action, $textblock) {
    require_once(IA_ROOT."common/textblock.php");

    $textsec = $textblock['security'];
    $usersec = getattr($user, 'security_level', 'anonymous');

    log_assert_valid(textblock_validate($textblock));

    // HACK: Forward security to user.
    // HACK: based on name
    if (preg_match("/^ ".preg_quote(TB_USER_PREFIX, '/').
                   " ([a-z0-9_\-]*) \/? .* $/xi", $textblock['name'], $matches)) {
        require_once(IA_ROOT . "common/db/user.php");
        $ouser = user_get_by_username($matches[1]);
        if ($ouser === null) {
            log_warn("User page for missing user");
            return false;
        }
        return security_user($user, $action, $ouser);
    }

    // Forward security to task.
    if (preg_match("/^ \s* task: \s* ([a-z0-9]*) \s* $/xi", $textsec, $matches)) {
        require_once(IA_ROOT . "common/db/task.php");
        $task = task_get($matches[1]);
        if ($task === null) {
            log_warn("Bad security descriptor, ask an admin.");
            return $usersec == 'admin';
        }
        return security_task($user, $action, $task);
    }

    // Forward security to round.
    if (preg_match("/^ \s* round: \s* ([a-z0-9]*) \s* $/xi", $textsec, $matches)) {
        require_once(IA_ROOT . "common/db/round.php");
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

    // Log query response.
    $action = security_simplify_action($action);
    $objid = $textblock['name'];
    log_print("SECURITY QUERY TEXTBLOCK: ".
            "($usersec, $action, $objid): ".
            "(level, action, object");

    switch ($action) {
        case 'simple-view':
            if ($textsec == 'private') {
                return $usersec == 'admin';
            } else {
                return true;
            }

        // Reversible modifications.
        case 'simple-rev-edit':
            if ($textsec == 'public') {
                return $usersec != 'anonymous';
            } else {
                return $usersec == 'admin';
            }

        // Permanent changes. Admin only
        case 'simple-edit':
        case 'simple-critical':
            return $usersec == 'admin';

        default:
            log_error('Invalid textblock action: '.$action);
    }
}

// Jump to security_textblock.
// FIXME: attach-grader?
function security_attach($user, $action, $attach) {
    //log_print_r($attach);
    // HACK: magic prefix.
    if (preg_match('/^grader\_/', $attach['name'])) {
        $newaction = preg_replace('/^attach/', 'grader', $action);
        log_print("SECURITY: CONVERTING $action to $newaction");
        $action = $newaction;
    }
    return security_textblock($user, $action, textblock_get_revision($attach['page']));
}

// FIXME: more?
function security_user($user, $action, $target_user) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_self = $target_user['id'] == $user['id'];

    $action = security_simplify_action($action);

    switch ($action) {
        case 'simple-view':
            return true;

        case 'simple-rev-edit':
        case 'simple-edit':
        case 'user-editprofile':
            // anyone can edit their own profile. admins can edit any profile
            return $is_admin || $is_self;

        case 'simple-critical':
            return $is_admin;

        default:
            log_error('Invalid user action: '.$action);
            return false;
    }
}

// FIXME: round logic.
function security_task($user, $action, $task) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_owner = ($task['user_id'] == $user['id'] && $usersec == 'helper');

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' : 'other'));
    $objid = $task['id'];
    log_print("SECURITY QUERY TASK: ".
            "($level, $action, $objid): ".
            "(level, action, object");

    switch ($action) {
        // Read-only access.
        case 'simple-view':
            return ($task['hidden'] == false) || $is_owner || $is_admin;

        // Edit access.
        case 'simple-rev-edit':
            return $is_owner || $is_admin;

        case 'simple-edit':
            return ($task['hidden'] == false && $is_owner) || $is_admin;

        // Admin stuff:
        case 'simple-critical':
            return $is_admin;

        // Special: submit.
        // FIXME: contest logic?
        case 'task-submit':
            if ($usersec == 'anonymous') {
                return false;
            }
            return ($task['hidden'] == false) || $is_owner || $is_admin;

        default:
            log_error('Invalid task action: '.$action);
    }
}

// FIXME: contest logic.
function security_round($user, $action, $round) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_owner = ($round['user_id'] == $user['id'] && $usersec == 'helper');

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' : 'other'));
    $objid = $round['id'];
    log_print("SECURITY QUERY ROUND: ".
            "($level, $action, $objid): ".
            "(level, action, object");


    switch ($action) {
        // Read-only access.
        case 'simple-view':
            return ($round['hidden'] == false) || $is_owner || $is_admin;

        // Edit access.
        case 'simple-rev-edit':
            return $is_owner || $is_admin;

        case 'simple-edit':
            return ($round['hidden'] == false && $is_owner) || $is_admin;

        // Admin stuff:
        case 'simple-critical':
            return $is_admin;

        // Special: submit.
        // FIXME: contest logic?
        case 'round-submit':
            if ($usersec == 'anonymous') {
                return false;
            }
            return ($round['hidden'] == false) || $is_owner || $is_admin;

        default:
            log_error('Invalid round action: '.$action);
    }
}

// FIXME: macro security is stupid.
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
    }
}

?>
