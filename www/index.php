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
$page_path = explode('/', $page);

$url_root = getattr($page_path, 0, '');
$page_id = implode('/', array_slice($page_path, 1));
$action = request('action', 'view');

// Check if page gets passed to a controller or is a simple textblock
if (in_array($url_root, $IA_CONTROLLERS)) {
    // Trivial direct mappings
    if (in_array($url_root, $IA_DIRECT_CONTROLLERS)) {
        require_once(IA_ROOT_DIR."www/controllers/{$url_root}.php");
        $fname = "controller_{$url_root}";
        $fname($page_id);
    }

    // Account edit page
    if ($url_root == 'account') {
        require_once(IA_ROOT_DIR.'www/controllers/account.php');
        controller_account(getattr($page_path, 1));
    }

    // Admin controller
    if ($url_root == 'admin') {
        $subcontroller = getattr($page_path, 1);

        // Blog admin
        if ($subcontroller == 'blog') {
            $obj_id = implode("/", array_slice($page_path, 1));
            require_once(IA_ROOT_DIR.'www/controllers/blog.php');
            controller_blog_admin($obj_id);
        }

        // Task creator
        if ($subcontroller == 'problema-noua') {
            require_once(IA_ROOT_DIR.'www/controllers/task.php');
            controller_task_create();
        }

        // Task detail editor
        if ($subcontroller == 'problema') {
            $obj_id = implode("/", array_slice($page_path, 2));
            require_once(IA_ROOT_DIR.'www/controllers/task.php');
            controller_task_details($obj_id);
        }

        // Round creator
        if ($subcontroller == 'runda-noua') {
            require_once(IA_ROOT_DIR.'www/controllers/round.php');
            controller_round_create();
        }

        // Round detail editor
        if ($subcontroller == 'runda') {
            $obj_id = implode("/", array_slice($page_path, 2));
            require_once(IA_ROOT_DIR.'www/controllers/round.php');
            controller_round_details($obj_id);
        }

        // Invalid subcontroller
        flash_error('URL invalid');
        redirect(url_home());
    }

    if ($url_root == 'inregistrare-runda') {
        $obj_id = implode("/", array_slice($page_path, 1));
        require_once(IA_ROOT_DIR.'www/controllers/round_register.php');
        controller_round_register($obj_id);
    }

    // Round registered users
    if ($url_root == 'lista-inregistrare') {
        $obj_id = implode("/", array_slice($page_path, 1));
        require_once(IA_ROOT_DIR.'www/controllers/round_register.php');
        controller_round_register_view($obj_id);
    }

    // Blog controller
    if ($url_root == 'blog')
    {
        // Blog index
        if ($page == 'blog') {
            // Blog RSS feed
            if ($action == 'rss') {
                require_once(IA_ROOT_DIR.'www/controllers/blog.php');
                controller_blog_feed();
            } else {
                require_once(IA_ROOT_DIR.'www/controllers/blog.php');
                controller_blog_index();
            }
        }
        
        // Blog edit
        if ($action == 'edit') {
            require_once(IA_ROOT_DIR.'www/controllers/textblock_edit.php');
            controller_textblock_edit($page, 'private');
        }

        // Blog view
        if ($action == 'view') {
            require_once(IA_ROOT_DIR.'www/controllers/blog.php');
            controller_blog_view($page, request('revision'));
        }


        // Invalid url
        flash_error('URL invalid');
        redirect(url_blog());
    }

    // Password reset confirmation
    if ($url_root == 'confirm') {
        require_once(IA_ROOT_DIR.'www/controllers/resetpass.php');
        controller_resetpass_confirm($page_id);
    }
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
//  - attachment rename
else if ($action == 'attach-rename') {
    require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
    controller_attachment_rename($page, $_POST['old_name'], $_POST['new_name']);
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

// user profile, view personal page / statistics / rating evolution
else if (IA_USER_TEXTBLOCK_PREFIX == $url_root.'/' &&
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
