<?php

require_once(IA_ROOT_DIR."common/db/round.php");


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
//  - helper        Trusted users. They can make their own tasks, but
//                  can't publish them. For teachers or high ratings.
//  - admin         Can do anything. For core team members.

// Returns boolean whether $user can perform $action onto $object
function security_query($user, $action, $object) {
    list($group, $subaction) = explode('-', $action, 2);

    log_assert(is_array($object), '$object must be an array');
    // Log security checking.
    $username = getattr($user, 'username', 'null');
    $usersec = getattr($user, 'security_level', 'anonymous');
    $object_id = getattr($object, 'id', getattr($object, 'name', $object));
    if (IA_LOG_SECURITY) {
        log_print("SECURITY QUERY: ".
                "($username, $usersec, $action, $object_id): ".
                "(username, level, action, object)");
    }

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
    if (IA_LOG_SECURITY) {
        if ($result) {
            log_print("SECURITY: GRANTED");
        } else {
            log_print("SECURITY: DENIED");
        }
    }
    return $result;
}

// This function simplifies $action.
// It's not an error to pass an already simplified action.
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
        case 'simple-view':
        case 'round-register-view':
            return 'simple-view';

        // Reversible edits access.
        case 'textblock-edit':
        case 'textblock-restore':
        case 'textblock-attach':
        case 'textblock-create':
        case 'simple-rev-edit':
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
        case 'grader-overwrite':
        case 'grader-delete':
        case 'grader-download':
        case 'simple-edit':
            return 'simple-edit';

        // Admin stuff:
        case 'task-change-security':
        case 'textblock-change-security':
        case 'simple-critical':
            return 'simple-critical';

        // Special actions fall through
        // FIXME: As few as possible.
        case 'task-submit':
        case 'round-view-tasks':
        case 'round-register':
        case 'user-editprofile':
        case 'user-change-security':
        case 'job-view':
        case 'job-download': 
            return $action;

        default:
            log_error('Invalid action: '.$action);
    }
}

// Handles textblock security.
function security_textblock($user, $action, $textblock) {
    require_once(IA_ROOT_DIR."common/textblock.php");

    $textsec = $textblock['security'];
    $usersec = getattr($user, 'security_level', 'anonymous');

    log_assert_valid(textblock_validate($textblock));

    // HACK: Forward security to user.
    // HACK: based on name
    if (preg_match("/^ ".
                preg_quote(IA_USER_TEXTBLOCK_PREFIX, '/').
                '('.IA_RE_USER_NAME.") (\/?.*) $/xi",
                $textblock['name'], $matches)) {
        require_once(IA_ROOT_DIR . "common/db/user.php");
        $ouser = user_get_by_username($matches[1]);
        if ($ouser === null) {
            log_warn("User page for missing user");
            return false;
        }
        // This is a horrible hack to prevent deleting or moving an user page.
        // This is pure evil.
        if ($matches[2] != '') {
            return false;
        }
        if ($action == 'textblock-delete' || $action == 'textblock-move') {
            $action = 'simple-critical';
        }
        return security_user($user, $action, $ouser);
    }

    // Forward security to task.
    if (preg_match("/^ \s* task: \s* (".IA_RE_TASK_ID.") \s* $/xi", $textsec, $matches)) {
        require_once(IA_ROOT_DIR . "common/db/task.php");
        $task = task_get($matches[1]);
        if ($task === null) {
            log_warn("Bad security descriptor, ask an admin.");
            return $usersec == 'admin';
        }
        return security_task($user, $action, $task);
    }

    // Forward security to round.
    if (preg_match("/^ \s* round: \s* (".IA_RE_ROUND_ID.") \s* $/xi", $textsec, $matches)) {
        require_once(IA_ROOT_DIR . "common/db/round.php");
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
    if (IA_LOG_SECURITY) {
        log_print("SECURITY QUERY TEXTBLOCK: ".
                "($usersec, $action, $objid): ".
                "(level, action, object");
    }

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

    $att_name = strtolower($attach['name']);
    $att_page = normalize_page_name($attach['page']);

    // HACK: magic prefix.
    if (preg_match('/^grader\_/', $att_name)) {
        $newaction = preg_replace('/^attach/', 'grader', $action);
        if (IA_LOG_SECURITY) {
            log_print("SECURITY: CONVERTING $action to $newaction");
        }
        $action = $newaction;
    }
   
    // Speed hack: avatars are always visible. This is good.
    if ($action == 'attach-download' && $att_name = 'avatar' &&
            strstr($att_page, IA_USER_TEXTBLOCK_PREFIX) === $att_page) {
        //log_print("Speed hack, attachments always visible");
        return true;
    }

    // Forward to textblock.
    $tb = textblock_get_revision($attach['page']);
    if (!$tb) {
        log_print_r($attach);
    }
    log_assert($tb, "Orphan attachment");

    return security_textblock($user, $action, $tb);
}

// FIXME: more?
function security_user($user, $action, $target_user) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_self = $target_user['id'] == $user['id'];

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_self ? 'self' : 'other'));
    $objid = $target_user['username'];
    if (IA_LOG_SECURITY) {
        log_print("SECURITY QUERY USER: ".
                  "($level, $action, $objid): ".
                  "(level, action, object)");
    }

    switch ($action) {
        case 'simple-view':
            return true;

        case 'simple-rev-edit':
        case 'simple-edit':
        case 'user-editprofile':
            // anyone can edit their own profile. admins can edit any profile
            return $is_admin || $is_self;

        // FIXME: haaaaack.
        case 'user-change-security':
            return $is_admin;

        // Nobody is allowed here. This includes moving/deleting user's own
        // page and changing security descriptors in user pages.
        case 'simple-critical':
            return false;

        default:
            log_error('Invalid user action: '.$action);
            return false;
    }
}

// FIXME: contest logic.
function security_task($user, $action, $task) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_owner = ($task['user_id'] == $user['id'] && $usersec == 'helper');

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' : 'other'));
    $objid = $task['id'];
    if (IA_LOG_SECURITY) {
        log_print("SECURITY QUERY TASK: ".
                "($level, $action, $objid): ".
                "(level, action, object)");
    }

    switch ($action) {
        // Read-only access.
        case 'simple-view':
            return ($task['hidden'] == false) || $is_owner || $is_admin;

        // Edit access.
        case 'simple-rev-edit':
            return $is_owner || $is_admin;

        case 'simple-edit':
            return (/*$task['hidden'] &&*/ $is_owner) || $is_admin;
            //FIXME: Why not uncomment that? 

        // Admin stuff:
        case 'simple-critical':
            return $is_admin;

        // Special: submit. Check for at least one registered contest for the task.
        // FIXME: contest logic?
        case 'task-submit':
            //FIXME: this is ugly
            if ($usersec == 'anonymous') {
                return false;
            }
            $is_running = false;
            $rounds = task_get_parent_rounds($task['id']);
            foreach ($rounds as $round_id) {
                $round = round_get($round_id);
                if ($round['state'] != 'running') {
                    continue;
                }
                $is_running = true;
                break;
            }
            return ($task['hidden'] == false && $is_running) || $is_owner || $is_admin;

        default:
            log_error('Invalid task action: '.$action);
    }
}

// FIXME: contest logic.
function security_round($user, $action, $round) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : 'other');
    $objid = $round['id'];
    if (IA_LOG_SECURITY) {
        log_print("SECURITY QUERY ROUND: ".
                "($level, $action, $objid): ".
                "(level, action, object)");
    }

    switch ($action) {
        case 'simple-view':
            return true;
        
        case 'round-view-tasks':
            return $round['state'] != 'waiting' || $is_admin;

        case 'simple-rev-edit':
        case 'simple-edit':
        case 'simple-critical':
            return $is_admin;

        case 'round-register':
            if ($usersec == 'anonymous') {
                return false;
            }
            // FIXME: improve round registration logic
            $is_waiting = $round['state'] == 'waiting';
            return $is_waiting || $is_admin;

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
    $is_admin = $usersec == 'admin';
    $is_owner = ($job['user_id'] == $user['id']);
    //FIXME: I'm not sure this belongs here, but it's an easy way out :|
    $is_task_owner = ($job['task_owner_id'] == $user['id'] && $usersec == 'helper');
    $can_view_job = ($job['task_hidden'] == false) || $is_task_owner || $is_admin;

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' : ($is_task_owner ? 'task-owner' : 'other')));
    $objid = $job['id'];
    if (IA_LOG_SECURITY) {
        log_print("SECURITY QUERY JOB: ".
                "($level, $action, $objid): ".
                "(level, action, object)");
    }

    switch ($action) {
        case 'job-view':
            return $can_view_job;

        case 'job-download': //FIXME: this should be job-view-source, job-download is too confusing
            return $can_view_job && ($is_admin || $is_owner);

        default:
            log_error('Invalid job action: '.$action);
    }
}

?>
