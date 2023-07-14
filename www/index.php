<?php
require_once '../Config.php';
require_once Config::ROOT.'www/config.php';
require_once Config::ROOT.'common/log.php';
require_once Config::ROOT.'common/common.php';
if (Config::DEVELOPMENT_MODE) {
    log_print('- -- --- ---- ----- Request: '.$_SERVER['QUERY_STRING']);
}
check_requirements();
require_once Config::ROOT.'common/security.php';
require_once Config::ROOT.'www/url.php';
require_once Config::ROOT.'www/utilities.php';
require_once Config::ROOT.'www/identity.php';
require_once Config::ROOT.'common/db/db.php';
db_connect();

require_once '../lib/Core.php';

// restore identity (if such a session exists)
identity_restore();

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');

// Redirect to home if in /
if ($page == '') {
    $page = 'home';
}

// Check page name.
if (!is_page_name($page)) {
    FlashMessage::addError('invalid URL');
    redirect(url_home());
}


// Prepare some vars for url handler.
// Filter empty path elements. Strips extra '/'s
$page = normalize_page_name($page);
$pagepath = explode('/', $page);

$urlstart = getattr($pagepath, 0, '');
$page_id = implode('/', array_slice($pagepath, 1));
$action = request('action', 'view');


// Direct mapping list
// Note: array_flip() flips keys with values in a dictionary.
// FIXME: change this to Romanian!
$directmaps = array_flip(array('register', 'changes',
                               'login', 'logout', 'json', 'job_detail',
                               'monitor', 'submit', 'userinfo',
                               'search', 'job_skip', 'resetpass', 'reeval',
                               'userwidget', 'statistici_problema',
                               'penalty', 'penalty_edit', 'user_control',
));

//
// Here comes the big url mapper.
// We include in the if statement to avoid an extra parsing load.
//

// Trivial direct mappings
if (isset($directmaps[$urlstart])) {
    require_once Config::ROOT."www/controllers/{$urlstart}.php";
    $fname = "controller_{$urlstart}";
    $fname($page_id);
}

// Account edit page
else if ($urlstart == 'account') {
    require_once Config::ROOT.'www/controllers/account.php';
    controller_account(getattr($pagepath, 1));
}

// Task creator
else if ($page == 'admin/problema-noua') {
    require_once Config::ROOT.'www/controllers/task.php';
    controller_task_create();
}

// Task deleter
else if ($page == 'admin/sterge-problema') {
    require_once Config::ROOT.'www/controllers/task.php';
    controller_task_delete(request('task_id'));
}

// Task search
else if ($page == 'cauta-probleme') {
    require_once Config::ROOT.'www/controllers/task.php';
    controller_task_search();
}

// Task edit parameters
else if ($urlstart == 'problema' && $action == 'task-edit-params') {
    require_once Config::ROOT.'www/controllers/task.php';
    $task_id = implode('/', array_slice($pagepath, 1));
    controller_task_details($task_id);
}

// Task edit tags
else if ($urlstart == 'problema' && $action == 'task-edit-tags') {
    require_once Config::ROOT.'www/controllers/task.php';
    $task_id = implode('/', array_slice($pagepath, 1));
    controller_task_tag($task_id);
}

// Task edit ratings
else if ($urlstart == 'problema' && $action == 'task-edit-ratings') {
    require_once Config::ROOT.'www/controllers/task.php';
    $task_id = implode('/', array_slice($pagepath, 1));
    controller_task_ratings($task_id);
}

// Task algorithm tags
else if ($page == 'admin/task-tags') {
    require_once Config::ROOT.'www/controllers/task_tags.php';
    if (request('action') == 'add') {
        controller_task_tags_add();
    } else if (request('action') == 'delete') {
        controller_task_tags_delete();
    } else if (request('action') == 'rename') {
        controller_task_tags_rename();
    } else {
        controller_task_tags();
    }
}

// Round creator
else if ($page == 'admin/runda-noua') {
    require_once Config::ROOT.'www/controllers/round.php';
    controller_round_create();
}

// Round edit parameters
else if ($urlstart == 'admin' && getattr($pagepath, 1) == 'runda' &&
         $action == 'edit-params') {
    require_once Config::ROOT.'www/controllers/round.php';
    $round_id = implode('/', array_slice($pagepath, 2));
    controller_round_details($round_id);
}

// Round edit task order
else if ($urlstart == 'admin' && getattr($pagepath, 1) == 'runda' &&
         $action == 'edit-task-order') {
    require_once Config::ROOT.'www/controllers/round.php';
    $round_id = implode('/', array_slice($pagepath, 2));
    controller_round_task_order($round_id);
}

// Round delete
else if ($urlstart == 'admin' && getattr($pagepath, 1) == 'runda' &&
         $action == 'sterge-runda') {
    require_once Config::ROOT.'www/controllers/round.php';
    require_once Config::ROOT.'www/controllers/textblock.php';
    $round_id = implode('/', array_slice($pagepath, 2));
    if (request('delete-pages')) {
        $v = request('textblocks');
        controller_textblock_delete_many($v, url_round_delete($round_id));
    } else if (request('delete-round')) {
        controller_round_delete($round_id);
    } else {
        controller_round_delete_view($round_id);
    }
}

// Round registration
else if ($urlstart == 'inregistrare-runda') {
    $obj_id = implode('/', array_slice($pagepath, 1));
    require_once Config::ROOT.'www/controllers/round_register.php';
    controller_round_register($obj_id);
}

// Round registered users
else if ($urlstart == 'lista-inregistrare') {
    $obj_id = implode('/', array_slice($pagepath, 1));
    require_once Config::ROOT.'www/controllers/round_register.php';
    controller_round_register_view($obj_id);
}

// textblock controllers
// FIXME: quick array of sorts?
//  - edit textblock
else if ($action == 'edit') {
    require_once Config::ROOT.'www/controllers/textblock_edit.php';
    controller_textblock_edit($page);
}
//  - delete textblock
else if ($action == 'delete') {
    require_once Config::ROOT.'www/controllers/textblock.php';
    controller_textblock_delete($page);
}
// - delete textblock revision
else if ($action == 'delete-revision') {
    require_once Config::ROOT.'www/controllers/textblock.php';

    $rev = request('revision');
    $rev_cnt = request('revision_count');
    controller_textblock_delete_revision($page, $rev, $rev_cnt);
}

//  - view textblock history
else if ($action == 'history') {
    require_once Config::ROOT.'www/controllers/textblock.php';
    controller_textblock_history($page);
}
//  - move textblock
else if ($action == 'move') {
    require_once Config::ROOT.'www/controllers/textblock_move.php';
    controller_textblock_move($page);
}
//  - copy textblock
else if ($action == 'copy') {
    require_once Config::ROOT.'www/controllers/textblock_copy.php';
    controller_textblock_copy($page);
}
//  - restore textblock
else if ($action == 'restore') {
    require_once Config::ROOT.'www/controllers/textblock.php';
    controller_textblock_restore($page, request('revision'));
}
//  - view textblock differences between revisions
else if ($action == 'diff') {
    require_once Config::ROOT.'www/controllers/textblock.php';
    controller_textblock_diff($page);
}

// attachment controllers
//  - create attachment
else if ($action == 'attach') {
    require_once Config::ROOT.'www/controllers/attachment.php';
    controller_attachment_create($page);
}
//  - print attachment list
else if ($action == 'attach-list') {
    require_once Config::ROOT.'www/controllers/attachment.php';
    controller_attachment_list($page);
}
//  - attachment delete
else if ($action == 'attach-del') {
    require_once Config::ROOT.'www/controllers/attachment.php';
    controller_attachment_delete($page, request('file'));
}
//  - attachment rename
else if ($action == 'attach-rename') {
    require_once Config::ROOT.'www/controllers/attachment.php';
    controller_attachment_rename($page,
                                 request('old_name'),
                                 request('new_name'));
}
//  - attachment download
else if ($action == 'download') {
    if (request('resize')) {
        require_once Config::ROOT.'www/controllers/image_attachment.php';
        // download resized image
        controller_attachment_resized_img($page,
                                          request('file'),
                                          request('resize'));
    } else {
        require_once Config::ROOT.'www/controllers/attachment.php';
        // regular file download
        controller_attachment_download($page,
                                       request('file'),
                                       request('safe_only', false) == 'true');
    }
} else if ($action == 'attach-bulk-action') {
    require_once Config::ROOT.'www/controllers/attachment.php';
    if (request('download')) {
        controller_attachment_download_zip($page, request_args());
    } else if (request('delete')) {
        controller_attachment_delete_many($page, request_args());
    }
}

// reset password
else if ('confirm' == $urlstart) {
    // confirm reset password
    require_once Config::ROOT.'www/controllers/resetpass.php';
    controller_resetpass_confirm($page_id);
}

// user profile, view personal page / statistics / rating evolution
else if (Config::USER_TEXTBLOCK_PREFIX == $urlstart.'/' &&
         ('view' == $action || 'rating' == $action || 'stats' == $action )) {
    require_once Config::ROOT.'www/controllers/user.php';
    controller_user_view($page_id, $action, request('revision'));
}

// general textblock view
else if ($action == 'view') {
    require_once Config::ROOT.'www/controllers/textblock.php';
    controller_textblock_view($page, request('revision'));
}

// invalid URL
else {
    FlashMessage::addError('URL invalid.');
    redirect(url_home());
}
