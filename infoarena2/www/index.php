<?php

// common/log.php checks this.
define("IA_INSIDE_WWW", true);

require_once("config.php");
require_once(IA_ROOT . "common/log.php");
require_once(IA_ROOT . "common/common.php");
log_print("- -- --- ---- ----- Request: ".$_SERVER['QUERY_STRING']);
check_requirements();

require_once(IA_ROOT . "common/db/db.php");
require_once(IA_ROOT . "common/permissions.php");

require_once("utilities.php");
require_once("identity.php");
require_once("wiki/wiki.php");
require_once("textblock.php");


// restore identity (if such a session exists)
identity_restore();

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');

if (!preg_match('/^([a-z0-9_\-\/]*)$/i', $page)) {
    flash_error('invalid URL');
    redirect(url(''));
}


// Redirect to home if in /
if ($page == "") {
    $page = "home";
}

// Prepare some vars for url handler.
$pagepath = explode('/', $page);
$urlstart = getattr($pagepath, 0, '');
$page_id = implode('/', array_slice($pagepath, 1));
$action = request('action', 'view');

// Direct mapping list
// Note: array_flip() flips keys with values in a dictionary.
$directmaps = array_flip(array('register', 'profile', 'page_index',
                               'login', 'logout', 'reset_pass', 'json',
                               'job_detail', 'monitor', 'submit', 'userinfo',
                               'round_register'));
//
// Here comes the big url mapper.
// We include in the if statement to avoid an extra parsing load.
//

// Trivial direct mappings.
if (isset($directmaps[$urlstart])) {
    require_once("controllers/{$urlstart}.php");
    $fname = "controller_{$urlstart}";
    $fname($page_id);
}

// Admin pages.
else if ($urlstart == 'admin') {
    // Task detail editor.
    $obj_id = implode("/", array_slice($pagepath, 2));
    if (getattr($pagepath, 1) == 'task') {
        require_once('controllers/task.php');
        if ($action == 'save') {
            controller_task_save_details($obj_id);
        } else {
            controller_task_edit_details($obj_id);
        }
    }
    // Round detail editor.
    if (getattr($pagepath, 1) == 'round') {
        require_once('controllers/round.php');
        if ($action == 'save') {
            controller_round_edit_details($obj_id);
        } else {
            controller_round_save_details($obj_id);
        }
    }
}

// FIXME: convert to direct mapping.
else if ($urlstart == 'news_feed') {
    require_once("controllers/news.php");
    controller_news_view_feed();
}

// textblock controllers
//  - edit textblock
else if ($action == 'edit') {
    require_once('controllers/textblock.php');
    controller_textblock_edit($page);
}
//  - save textblock
else if ($action == 'save') {
    require_once('controllers/textblock.php');
    controller_textblock_save($page);
}
//  - delete textblock
else if ($action == 'delete') {
    require_once('controllers/textblock.php');
    controller_textblock_delete($page);
}
//  - view textblock history
else if ($action == 'history') {
    require_once('controllers/textblock.php');
    controller_textblock_history($page);
}
//  - restore textblock
else if ($action == 'restore') {
    require_once('controllers/textblock.php');
    controller_textblock_restore_revision($page, request('revision'));
}
//  - view textblock differences between revisions
else if ($action == 'diff') {
    require_once('controllers/textblock.php');
    controller_textblock_diff_revision($page, request('revision'));
}
//  - view textblock feed
else if ($action=='feed') {
    require_once('controllers/textblock.php');
    controller_textblock_feed($page);
}

// attachment controllers
//  - create attachment
else if ($action == 'attach') {
    require_once('controllers/attachment.php');
    controller_attachment_create($page);
}
//  - save attachment
else if ($action == 'attach-submit') {
    require_once('controllers/attachment.php');
    controller_attachment_submit($page);
}
//  - print attachment list
else if ($action == 'attach-list') {
    require_once('controllers/attachment.php');
    controller_attachment_list($page);
}
//  - attachment delete
else if ($action == 'attach-del') {
    require_once('controllers/attachment.php');
    controller_attachment_delete($page);
}
//  - attachment download
else if ($action == 'download') {
    if (request('resize')) {
        require_once('controllers/image_attachment.php');
        // download resized image
        controller_attachment_resized_img($page, request('file'), request('resize'));
    }
    else {
        require_once('controllers/attachment.php');
        // regular file download
        controller_attachment_download($page, request('file'));
    }
}

// textblock view
else if ($action == 'view') {
    require_once('controllers/textblock.php');
    controller_textblock_view($page, request('revision'));
}

// invalid URL
else {
    flash_error('URL invalid');
    redirect(url(''));
}

?>
