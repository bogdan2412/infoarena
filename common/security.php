<?php

require_once Config::ROOT.'common/db/user.php';
require_once Config::ROOT.'common/db/round.php';
require_once Config::ROOT.'common/db/task.php';
require_once Config::ROOT.'common/textblock.php';


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
//  - interns       Trusted users, future core team members.
//  - admin         Can do anything. For core team members.

// Returns boolean whether $user can perform $action onto $object
function security_query($user, $action, $object) {
    list($group, $subaction) = explode('-', $action, 2);

    log_assert(is_array($object) || is_null($object),
               '$object must be an array or null');
    // Log security checking.
    $username = getattr($user, 'username', 'null');
    $usersec = getattr($user, 'security_level', 'anonymous');
    $object_id = getattr($object, 'id', getattr($object, 'name', $object));
    if (Config::LOG_SECURITY) {
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
            log_error('Invalid action group: "'.$group.'"');
            break;
    }

    log_assert(is_bool($result), "SECURITY: FAILED, didn't return a bool");
    if (Config::LOG_SECURITY) {
        if ($result) {
            log_print('SECURITY: GRANTED');
        } else {
            log_print('SECURITY: DENIED');
        }
    }
    return $result;
}

// This function simplifies $action.
// It's not an error to pass an already simplified action.
function security_simplify_action($action) {
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

        // View IP.
        case 'attach-view-ip':
        case 'textblock-view-ip':
        case 'job-view-ip':
        case 'grader-view-ip':
            return 'sensitive-info';

        // Reversible edits access.
        case 'textblock-edit':
        case 'textblock-restore':
        case 'textblock-attach':
        case 'textblock-create':
        case 'textblock-copy':
        case 'simple-rev-edit':
            return 'simple-rev-edit';

        // Irreversible edits.
        case 'textblock-move':
        case 'attach-overwrite':
        case 'attach-delete':
        case 'attach-rename':
        case 'task-edit':
        case 'task-create':
        case 'task-delete':
        case 'task-tag':
        case 'task-edit-ratings':
        case 'textblock-delete':
        case 'textblock-delete-revision':
        case 'round-tag':
        case 'round-view-progress':
        case 'grader-overwrite':
        case 'grader-delete':
        case 'grader-rename':
        case 'simple-edit':
            return 'simple-edit';

        // Admin stuff:
        case 'task-change-security':
        case 'task-change-open':
        case 'textblock-change-security':
        case 'textblock-tag':
        case 'textblock-global-history':
        case 'round-delete':
        case 'task-edit-owner':
        case 'simple-critical':
        case 'job-liveeval':
            return 'simple-critical';

        // Special actions fall through
        // FIXME: As few as possible.
        case 'grader-download':
        case 'task-use-in-user-round':
        case 'task-submit':
        case 'task-view-last-score':
        case 'task-view-statistics':
        case 'round-edit':
        case 'round-create':
        case 'round-submit':
        case 'round-view-tasks':
        case 'round-view-scores':
        case 'round-acm-view-partial-scores':
        case 'round-register':
        case 'user-editprofile':
        case 'user-change-security':
        case 'user-tag':
        case 'job-view':
        case 'job-eval':
        case 'job-view-source':
        case 'job-view-source-size':
        case 'job-view-score':
        case 'job-view-partial-feedback':
        case 'task-view-tags':
        case 'job-skip':
            return $action;

        default:
            log_error('Invalid action: '.$action);
            break;
    }
}

// Handles textblock security.
function security_textblock($user, $action, $textblock) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    if (!is_null($textblock)) {
        log_assert_valid(textblock_validate($textblock));
        $textsec = $textblock['security'];

        // HACK: Forward security to user based on name.
        if (count($matches = get_page_user_name($textblock['name'])) > 0) {
            $ouser = user_get_by_username($matches[1]);
            if ($ouser === null) {
                log_warn('User page for missing user');
                return false;
            }
            // This is a horrible hack to prevent deleting or moving an
            // user page. This is pure evil.
            if ($action == 'textblock-delete' || $action == 'textblock-move') {
                $action = 'simple-critical';
            }
            return security_user($user, $action, $ouser);
        }

        // Forward security to task.
        if (($task_id = textblock_security_is_task($textsec))) {
            $task = task_get($task_id);
            if ($task === null) {
                log_warn('Bad security descriptor, ask an admin.');
                return $usersec == 'admin';
            }
            return security_task($user, $action, $task);
        }

        // Forward security to round.
        if (($round_id = textblock_security_is_round($textsec))) {
            $round = round_get($round_id);
            if ($round === null) {
                log_warn('Bad security descriptor, ask an admin.');
                return $usersec == 'admin';
            }
            return security_round($user, $action, $round);
        }

        if (preg_match('/^ \s* (private|protected|public) \s* $/xi', $textsec,
                       $matches)) {
            $textsec = $matches[1];
        } else {
            log_warn('Bad security descriptor, ask an admin.');
            return $usersec == 'admin';
        }
    }

    // Log query response.
    $action = security_simplify_action($action);
    if (Config::LOG_SECURITY) {
        $objid = is_null($textblock) ? 'NULL' : $textblock['name'];
        log_print("SECURITY QUERY TEXTBLOCK: ".
                "($usersec, $action, $objid): ".
                "(level, action, object");
    }

    switch ($action) {
        case 'simple-view':
            if ($textsec == 'private') {
                return in_array($usersec, Config::SEC_VIEW_PRIVATE);
            } else {
                return true;
            }

        case 'sensitive-info':
            return in_array($usersec, array('admin', 'intern', 'helper'));

        // Reversible modifications.
        case 'simple-rev-edit':
            if ($textsec == 'public') {
                return in_array($usersec, Config::SEC_REV_EDIT_PUBLIC);
            } else {
                return in_array($usersec, Config::SEC_REV_EDIT_OTHER);
            }

        // Permanent changes. Admin only
        case 'simple-edit':
        case 'simple-critical':
            return $usersec == 'admin';

        default:
            log_error('Invalid textblock action: '.$action);
            break;
    }
}

// Jump to security_textblock.
// FIXME: attach-grader?
function security_attach($user, $action, $attach) {
    $att_name = $attach['name'];
    $att_page = normalize_page_name($attach['page']);
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_owner = $attach['user_id'] == getattr($user, 'id');

    // Log query response.
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' : 'other'));
    if (Config::LOG_SECURITY) {
        $objid = $attach['user_id'];
        log_print("SECURITY QUERY ATTACH: ".
                  "($level, $action, $objid): ".
                  "(level, action, object)");
    }

    // Speed hack: avatars are always visible. This is good.
    if ($action == 'attach-download' && $att_name == 'avatar' &&
            starts_with($att_page, Config::USER_TEXTBLOCK_PREFIX)) {
        return true;
    }

    // Forward to textblock.
    $tb = textblock_get_revision($attach['page']);
    if (!$tb) {
        log_print_r($attach);
    }
    log_assert($tb, 'Orphan attachment');

    // Convert action into a grader action if the textblock is a task
    // textblock and the attachment has the grader_ prefix.
    if (textblock_security_is_task($tb['security']) &&
        preg_match('/^grader\_/', $att_name)) {
        $newaction = preg_replace('/^attach/', 'grader', $action);
        if (Config::LOG_SECURITY) {
            log_print("SECURITY: CONVERTING $action to $newaction");
        }
        $action = $newaction;
    }

    return security_textblock($user, $action, $tb);
}

// FIXME: more?
function security_user($user, $action, $target_user) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_self = $target_user['id'] == getattr($user, 'id');

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_self ? 'self' : 'other'));
    if (Config::LOG_SECURITY) {
        $objid = $target_user['username'];
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
        case 'user-tag':
            return $is_admin;

        // Nobody is allowed here. This includes moving/deleting user's own
        // page and changing security descriptors in user pages.
        case 'simple-critical':
            return false;

        case 'sensitive-info':
            return in_array($usersec, array('admin', 'intern', 'helper'));

        default:
            log_error('Invalid user action: '.$action);
            return false;
    }
}

// FIXME: contest logic.
function security_task($user, $action, $task) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_intern = $usersec == 'intern';
    $is_owner = $task && ($task['user_id'] == getattr($user, 'id') && $usersec == 'helper');
    $is_boss = $is_admin || $is_intern || $is_owner;

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' : 'other'));
    if (Config::LOG_SECURITY) {
        $objid = $task['id'];
        log_print("SECURITY QUERY TASK: ".
                "($level, $action, $objid): ".
                "(level, action, object)");
    }

    switch ($action) {
        // Read-only access.
        case 'simple-view':
            return ($task['security'] != 'private') || $is_boss;

        // Edit access.
        case 'simple-rev-edit':
            return $is_boss;

        case 'simple-edit':
            return $is_boss;

        // View tags
        case 'task-view-tags':
            return ($task['security'] == 'public') || $is_boss;

        // View statistics
        case 'task-view-statistics':
            return ($task['security'] == 'public') || $is_boss;

        // Admin stuff:
        case 'simple-critical':
            return $is_admin;

        case 'task-use-in-user-round':
            if ($usersec == 'anonymous') {
                return false;
            }
            return ($task['security'] == 'public') || $is_admin;

        // Special: submit. Check for at least one
        // registered contest for the task.
        // FIXME: contest logic?
        case 'task-submit':
            // FIXME: this is ugly
            if ($usersec == 'anonymous') {
                return false;
            }
            if ($is_boss) {
                return true;
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
            return ($task['security'] != 'private' && $is_running);

        case 'task-view-last-score':
            return $task['security'] == 'public' || $is_boss;

        case 'grader-download':
            return ($task['open_tests'] && $task['security'] == 'public')
                    || $is_boss;

        case 'sensitive-info':
            return $is_boss;

        default:
            log_error('Invalid task action: '.$action);
            break;
    }
}

// FIXME: contest logic.
function security_round($user, $action, $round) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_intern = $usersec == 'intern';

    // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : 'other');
    if (Config::LOG_SECURITY) {
        $objid = $round['id'];
        log_print("SECURITY QUERY ROUND: ".
                "($level, $action, $objid): ".
                "(level, action, object)");
    }

    switch ($action) {
        case 'simple-view':
          return true;

        case 'round-create':
          if ($round['type'] == 'user-defined') {
              return $usersec != 'anonymous';
          } else {
              return $is_admin || $is_intern;
          }

        case 'round-edit':
        case 'simple-rev-edit':
          if ($usersec == 'anonymous') {
              return false;
          }
          if ($round['type'] == 'user-defined') {
              return getattr($user, 'id') == $round['user_id'] ||
                  $is_admin ||
                  $is_intern;
          } else {
              return $is_admin || $is_intern;
          }

        case 'round-view-tasks':
            return $round['state'] != 'waiting' || $is_admin || $is_intern;
        case 'round-view-scores':
            return $round['public_eval'] == true || $is_admin || $is_intern;

        case 'simple-edit':
            return $is_admin || $is_intern;

        case 'simple-critical':
            return $is_admin;

        case 'round-register':
            if ($usersec == 'anonymous') {
                return false;
            }
            // FIXME: improve round registration logic
            $is_waiting = $round['state'] == 'waiting';
            return $is_waiting || $is_admin;

        case 'round-submit':
            return $round['state'] == 'running';

        case 'sensitive-info':
            return in_array($usersec, array('admin', 'intern', 'helper'));

        case 'round-acm-view-partial-scores':
            return $round['state'] != 'waiting' || $is_admin || $is_intern;

        default:
            log_error('Invalid round action: '.$action);
            break;
    }
}

// FIXME: macro security is stupid.
function security_macro($user, $action, $args) {
    $usersec = getattr($user, 'security_level', 'anonymous');

    switch ($action) {
        case 'macro-grep':
            return true;
        case 'macro-debug':
            // only administrators can execute these macros
            return $usersec == 'admin';

        default:
            log_error('Invalid macro action: '.$action);
            break;
    }
}

// There is no job-eval, jobs are evaluated on the spot,
// we check job-view instead.
function security_job($user, $action, $job) {
    $usersec = getattr($user, 'security_level', 'anonymous');
    $is_admin = $usersec == 'admin';
    $is_intern = $usersec == 'intern';
    $is_owner = $job && ($job['user_id'] == getattr($user, 'id'));
    $is_task_owner =
        $job &&
        ($job['task_owner_id'] == getattr($user, 'id')) &&
        in_array($usersec, array('helper', 'intern'));

     // Log query response.
    $action = security_simplify_action($action);
    $level = ($is_admin ? 'admin' : ($is_owner ? 'owner' :
        ($is_task_owner ? 'task-owner' : 'other')));
    if (Config::LOG_SECURITY) {
        $objid = getattr($job, 'id');
        log_print("SECURITY QUERY JOB: ".
                "($level, $action, $objid): ".
                "(level, action, object)");
    }
    if ($action == 'simple-critical') {
        return $is_admin || $is_intern;
    }

    if ($action == 'job-skip') {
        // FIXME: add extra people to do this, we're too few
        return $is_admin;
    }

    $can_view_job = ($job['task_security'] != 'private') || $is_task_owner
                 || $is_admin || $is_intern;
    $can_view_source =
        ($user && // need to add this explicitly because most tasks are in round  'arhiva'
         $job['task_security'] == 'public' &&
         $job['round_id'] &&
         $job['status'] != 'skipped') ||
        ($job['task_security'] == 'public' &&
         $job['task_open_source'] == true &&
         $job['status'] != 'skipped') ||
        $is_task_owner || $is_owner || $is_admin || $is_intern;
    $can_view_source_size = $can_view_source;
    $can_view_score = ($job['round_public_eval'] == true) ||
                       $is_task_owner || $is_admin || $is_intern;
    $can_view_partial_feedback = $is_owner || $is_admin || $is_intern;
    $can_view_sensitive_info = in_array($usersec,
                                        array('admin', 'intern', 'helper'));

    switch ($action) {
        case 'job-view':
            return $can_view_job;

        case 'job-view-source':
            return $can_view_job && $can_view_source;

        case 'job-view-source-size':
            return $can_view_job && $can_view_source_size;

        case 'job-view-score':
            return $can_view_job && $can_view_score;

        case 'job-view-partial-feedback':
            return $can_view_job && $can_view_partial_feedback;

        case 'sensitive-info':
            return $can_view_job && $can_view_sensitive_info;

        default:
            log_error('Invalid job action: '.$action);
            break;
    }
}
