<?php

require_once("../config.php");
require_once("config.php");
require_once("utilities.php");
require_once("identity.php");
require_once("wiki/wiki.php");
require_once("db.php");

// restore identity session (if such a session exists)
identity_restore();

// Do url validation.
// All urls that pass are valid, they can be missing wiki pages.
$page = request('page');
if (!preg_match('/^([a-z0-9_\-\/]*)$/i', $page)) {
    flash_error('Url invalid');
    redirect(url(''));
}

// Redirect to home if in /
if ($page == "") {
    $page = "home";
}

// Split the page url.
$urlpath = split('/', $page);
if (count($urlpath) <= 0) {
    $urlpath = array("");
}

// This is the first part of the url path
$urlstart = strtolower($urlpath[0]);

// A lot of logic depends on this, so we try to keep the code nicer.
$action = request('action', 'view');

// Direct mapping list
// Note: array_flip() flips keys with values in a dictionary.
$directmaps = array_flip(array('register', 'profile', 'page_index',
                               'login', 'logout', 'json'));

//
// Here comes the big url mapper.
// We include in the if statement to avoid an extra parsing load.
//

/*echo "<pre>urlstart = $urlstart</pre>";
echo "<pre>action = $action</pre>";
echo "<pre>directmaps = ";
print_r($directmaps);
echo "</pre>";*/

// Trivial direct mappings.
if (isset($directmaps[$urlstart])) {
    require("controllers/{$urlstart}.php");
    $fname = "controller_{$urlstart}";
    if (count($urlpath) < 2) {
        $suburl = "";
    } else {
        array_shift($urlpath);
        $suburl = join('/', $urlpath);
    }
    $fname($suburl);

// User special shit
} else if ($urlstart == 'user' && $action == 'download') {
    require('controllers/attachment.php');
    controller_attachment_download($page);
} else if ($urlstart == 'user') {
    require('controllers/user.php');
    if (count($urlpath) < 2) {
        $suburl = "";
    } else {
        array_shift($urlpath);
        $suburl = join('/', $urlpath);
    }
    controller_user($suburl);
// Special shit for task view edit create
} else if ($urlstart == 'task' && $action == 'view') {
    require('controllers/task.php');
    controller_task_view($urlpath[0]);
} else if ($urlstart == 'task' && $action == 'edit') {
    require('controllers/task.php');
    controller_task_edit($urlpath[0]);
} else if ($urlstart == 'task' && $action == 'create') {
    require('controllers/task.php');
    controller_task_create($urlpath[0]);

// Insert pset stuff here.
//
//  ---
//
} else if ($urlstart == 'news' && count($urlpath) == 1) {
    require('controllers/news.php');
    controller_news_view_all();
} else if ($urlstart == 'news' && $action == 'view') {
    require('controllers/news.php');
    controller_news_view($page, request('revision'));
} else if ($urlstart == 'news' && $action == 'edit') {
    require('controllers/news.php');
    controller_news_edit($page);
} else if ($urlstart == 'news' && $action == 'save') {
    require('controllers/news.php');
    controller_news_save($page);
    
// If it was not a special task or pset page do the wiki monkey.
} else if ($action == 'view') {
    require('controllers/wiki.php');
    controller_wiki_view($page, request('revision'));
} else if ($action == 'edit') {
    require('controllers/wiki.php');
    controller_wiki_edit($page);
} else if ($action == 'save') {
    require('controllers/wiki.php');
    controller_wiki_save($page);
} else if ($action == 'history') {
    require('controllers/textblock.php');
    controller_textblock_history($page);

// Attachment shit. This is common to all wiki-based urls.
} else if ($action == 'attach') {
    require('controllers/attachment.php');
    controller_attachment_create($page);
} else if ($action == 'attach-submit') {
    require('controllers/attachment.php');
    controller_attachment_submit($page);
} else if ($action == 'attach-list') {
    require('controllers/attachment.php');
    controller_attachment_list($page);
} else if ($action == 'attach-del') {
    require('controllers/attachment.php');
    controller_attachment_delete($page);
} else if ($action == 'download') {
    require('controllers/attachment.php');
    controller_attachment_download($page);
} else {
    flash_error('Url invalid');
    redirect(url(''));
}
?>
