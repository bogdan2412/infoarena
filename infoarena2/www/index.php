<?php
require_once("../config.php");
require_once(IA_ROOT_DIR."www/config.php");
require_once(IA_ROOT_DIR."common/log.php");
require_once(IA_ROOT_DIR."common/common.php");
if (IA_DEVELOPMENT_MODE) {
    log_print("- -- --- ---- ----- Request: ".$_SERVER['QUERY_STRING']);
}
check_requirements();
require_once(IA_ROOT_DIR."common/security.php");
require_once(IA_ROOT_DIR."www/url.php");
require_once(IA_ROOT_DIR."www/utilities.php");
require_once(IA_ROOT_DIR."www/identity.php");
require_once(IA_ROOT_DIR."common/db/db.php");
db_connect();

// restore identity (if such a session exists)
identity_restore();

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');

// Redirect to home if in /
if ($page == "") {
    $page = "home";
}

// Check page name.
if (!is_page_name($page)) {
    flash_error('invalid URL');
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
$directmaps = array_flip(array('register', 'news_feed', 'changes',
                               'login', 'logout', 'json', 'job_detail',
                               'monitor', 'submit', 'userinfo', 'plot',
                               'unsubscribe', 'resetpass'
));
//
// Here comes the big url mapper.
// We include in the if statement to avoid an extra parsing load.
//

// Trivial direct mappings.
if (isset($directmaps[$urlstart])) {
    require_once(IA_ROOT_DIR."www/controllers/{$urlstart}.php");
    $fname = "controller_{$urlstart}";
    $fname($page_id);
}

// Account edit page.
else if ($urlstart == 'account') {
    require_once(IA_ROOT_DIR.'www/controllers/account.php');
    controller_account(getattr($pagepath, 1));
}

// Task creator
else if ($page == 'admin/problema-noua') {
    require_once(IA_ROOT_DIR.'www/controllers/task.php');
    controller_task_create();
}

// Task detail editor.
else if ($urlstart == 'admin' && getattr($pagepath, 1) == 'problema') {
    $obj_id = implode("/", array_slice($pagepath, 2));
    require_once(IA_ROOT_DIR.'www/controllers/task.php');
    controller_task_details($obj_id);
}

// Round creator
else if ($page == 'admin/runda-noua') {
    require_once(IA_ROOT_DIR.'www/controllers/round.php');
    controller_round_create();
}

// Round detail editor.
else if ($urlstart == 'admin' && getattr($pagepath, 1) == 'runda') {
    $obj_id = implode("/", array_slice($pagepath, 2));
    require_once(IA_ROOT_DIR.'www/controllers/round.php');
    controller_round_details($obj_id);
}

// Round registration. 
// FIXME: This should not be hardcoded.
else if ($urlstart == 'inregistrare-runda') {
    $obj_id = implode("/", array_slice($pagepath, 1));
    require_once(IA_ROOT_DIR.'www/controllers/round_register.php');
    controller_round_register($obj_id);
}

// FIXME: This should not be hardcoded.
else if ($urlstart == 'lista-inregistrare') {
    $obj_id = implode("/", array_slice($pagepath, 1));
    require_once(IA_ROOT_DIR.'www/controllers/round_register.php');
    controller_round_register_view($obj_id);
}

// textblock controllers
// FIXME: quick array of sorts?
//  - edit textblock
else if ($action == 'edit') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock_edit.php');
    controller_textblock_edit($page);
}
//  - delete textblock
else if ($action == 'delete') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock.php');
    controller_textblock_delete($page);
}
//  - view textblock history
else if ($action == 'history') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock.php');
    controller_textblock_history($page);
}
//  - move textblock
else if ($action == 'move') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock_move.php');
    controller_textblock_move($page);
}
//  - restore textblock
else if ($action == 'restore') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock.php');
    controller_textblock_restore($page, request('revision'));
}
//  - view textblock differences between revisions
else if ($action == 'diff') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock.php');
    controller_textblock_diff($page);
}

// attachment controllers
//  - create attachment
else if ($action == 'attach') {
    require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
    controller_attachment_create($page);
}
//  - print attachment list
else if ($action == 'attach-list') {
    require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
    controller_attachment_list($page);
}
//  - attachment delete
else if ($action == 'attach-del') {
    require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
    controller_attachment_delete($page);
}
//  - attachment download
else if ($action == 'download') {
    if (request('resize')) {
        require_once(IA_ROOT_DIR.'www/controllers/image_attachment.php');
        // download resized image
        controller_attachment_resized_img($page, request('file'), request('resize'));
    } else {
        require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
        // regular file download
        controller_attachment_download($page, request('file'));
    }
}

// reset password
else if ('confirm' == $urlstart) {
    // confirm reset password
    require_once(IA_ROOT_DIR.'www/controllers/resetpass.php');
    controller_resetpass_confirm($page_id);
}

// user profile, view personal page / statistics / rating evolution
else if (IA_USER_TEXTBLOCK_PREFIX==$urlstart.'/' &&
        ('view' == $action || 'rating' == $action || 'stats' == $action )) {
    require_once(IA_ROOT_DIR.'www/controllers/user.php');
    controller_user_view($page_id, $action, request('revision'));
}

// general textblock view
else if ($action == 'view') {
    require_once(IA_ROOT_DIR.'www/controllers/textblock.php');
    controller_textblock_view($page, request('revision'));
}

// invalid URL
else {
    flash_error('URL invalid');
    redirect(url_home());
}

?>
