<?php

require_once(Config::ROOT . 'www/config.php');
require_once(Config::ROOT . 'common/common.php');

// Creates URLs to various parts of the infoarena website.
// Please avoid hard-coding URLs throughout the code.

// Compute complex url. Avoid using this function directly, prefer more
// specific url_ functions.
//
// The params array contains http get parameter,
// it's formatted in the end result as a series
// of key1=value1&key2=value2.
//
// NOTE: don't add ?x=y stuff in $document
//
// If $absolute is true(default false) then Config::URL_HOST will be included in
// the url.
function url_complex($document = '', $args = array(), $absolute = false) {
    $document = $document ?? '';
    log_assert(false === strpos($document, '?'), 'Page name contains ?');
    log_assert(is_array($args), "Argument list must be an array");
    log_assert(!array_key_exists("page", $args), "Argument list contains page");
    $args['page'] = $document;
    $url = url_from_args($args, $absolute);
    if ($absolute) {
        return url_absolute($url);
    } else  {
        return $url;
    }
}

// Makes an url absolute. It just prepends Config::URL_HOST
function url_absolute($url) {
    log_assert(strpos($url, 'http') !== 0, "Url begins with http");
    $url = Config::URL_HOST . $url;
    return $url;
}

// Construct an URL from an argument list.
// These are the exact $args you will receive in $_GET
function url_from_args($args) {
    // First part.
    $url = Config::URL_PREFIX . getattr($args, "page", "home");

    // Actual args.
    $first = true;
    foreach ($args as $k => $v) {
        if (is_null($v)) {
            continue;
        }
        if ($k != 'page') {
            if (is_array($v)) {
                foreach ($v as $sv) {
                    $url .= ($first ? "?" : "&");
                    $first = false;
                    $url .= sprintf('%s%s=%s',
                                    $k, urlencode('[]'), urlencode($sv));
                }
            } else {
                $url .= ($first ? "?" : "&");
                $first = false;
                $url .= $k . '=' . urlencode($v);
            }
        }
    }
    return $url;
}

// Here are the specific url functions you should use.
// Names should should be more or less obvious.

// First: textblocks(wiki pages).

function url_textblock($page_name) {
    return url_complex($page_name, array());
}

function url_textblock_revision($page_name, $rev) {
    return url_complex($page_name, array('revision' => $rev));
}

function url_textblock_edit($page_name) {
    return url_complex($page_name, array('action' => 'edit'));
}

function url_textblock_history($page_name) {
    return url_complex($page_name, array('action' => 'history'));
}

function url_textblock_diff($page_name, $revfrom, $revto) {
    return url_complex($page_name, array(
            'action' => 'diff',
            'rev_from' => $revfrom,
            'rev_to' => $revto,
    ));
}

function url_textblock_move($page_name) {
    return url_complex($page_name, array(
            'action' => 'move'
    ));
}

function url_textblock_copy($page_name) {
    return url_complex($page_name, array(
            'action' => 'copy'
    ));
}

function url_textblock_delete($page_name) {
    return url_complex($page_name, array(
            'action' => 'delete'
    ));
}

function url_textblock_restore($page_name, $rev) {
    return url_complex($page_name, array(
            'action' => 'restore',
            'revision' => $rev,
    ));
}

function url_textblock_delete_revision($page_name, $rev) {
    return url_complex($page_name, array(
            'action' => 'delete-revision',
            'revision' => $rev
    ));
}

// Textblock attachments:

function url_attachment($page, $file, $restrict_to_safe_mime_types = false) {
    log_assert(is_page_name($page));
    log_assert(is_attachment_name($file));
    return url_complex($page, array(
            'action' => 'download',
            'file' => $file,
            'safe_only' => ($restrict_to_safe_mime_types ? 'true' : 'false'),
    ));
}

function url_attachment_new($page_name) {
    log_assert(is_page_name($page_name));
    return url_complex($page_name, array('action' => 'attach'));
}

function url_attachment_list($page_name) {
    log_assert(is_page_name($page_name));
    return url_complex($page_name, array('action' => 'attach-list'));
}

function url_attachment_delete($page_name, $file_name) {
    log_assert(is_page_name($page_name));
    return url_complex($page_name, array(
                'action' => 'attach-del',
                'file' => $file_name,
    ));
}

function url_attachment_rename($page_name) {
    log_assert(is_page_name($page_name));
    return url_complex($page_name, array('action' => 'attach-rename'));
}

function url_image_resize($page, $file, $resize) {
    if ($resize) {
        return url_complex($page, array(
                'action' => 'download',
                'file' => $file,
                'resize' => $resize,
        ));
    } else {
        return url_attachment($page, $file, true);
    }
}

// User stuff

function url_login() {
    return url_complex("login", array(), true, true);
}

function url_logout() {
    return url_complex("logout");
}

function url_account($user = false) {
    if ($user === false) {
        return url_complex("account");
    } else {
        return url_complex("account/$user");
    }
}

function url_register() {
    return url_complex("register", array(), true, true);
}

function url_resetpass($username = false) {
    if ($username === false) {
        return url_complex('resetpass');
    } else {
        return url_complex('resetpass/'.$username);
    }
}

function url_resetpass_confirm($username, $key) {
    log_assert($key);
    return url_complex('confirm/'.$username, array('c' => $key));
}

function url_user_profile($username) {
    return url_complex(Config::USER_TEXTBLOCK_PREFIX . $username, array(), true);
}

function url_user_rating($username) {
    return url_complex(Config::USER_TEXTBLOCK_PREFIX . $username, array(
            'action' => 'rating'
    ), true);
}

function url_user_stats($username) {
    return url_complex(Config::USER_TEXTBLOCK_PREFIX . $username, array(
            'action' => 'stats'
    ));
}

/**
 * Returns an url to an user's avatar with a given size
 *
 * @param  string  $username
 * @param  string  $size_type
 * @return string
 */
function url_user_avatar($username, $size_type = "full") {
    return url_complex("avatar/".$size_type."/".$username);
}

// Task/round stuff.

function url_task($task_id) {
    log_assert(is_task_id($task_id));
    return url_complex("problema/$task_id");
}

function url_task_edit($task_id, $action = 'edit') {
    log_assert(is_task_id($task_id));
    return url_complex("problema/$task_id", array('action' => $action));
}

function url_task_create() {
    return url_complex("admin/problema-noua");
}

function url_task_delete() {
    return url_complex("admin/sterge-problema");
}

function url_task_search($tag_ids) {
    log_assert(is_array($tag_ids), "Tag ids must be an array");
    return url_complex("cauta-probleme", array("tag_id" => $tag_ids));
}

function url_task_tags() {
    return url_complex("admin/task-tags");
}

function url_task_tags_add() {
    return url_complex("admin/task-tags", array("action" => "add"));
}

function url_task_tags_delete() {
    return url_complex("admin/task-tags", array("action" => "delete"));
}

function url_task_tags_rename() {
    return url_complex("admin/task-tags", array("action" => "rename"));
}

function url_round($round_id) {
    log_assert(is_round_id($round_id));
    $round = round_get($round_id);
    return url_complex($round['page_name']);
}

function url_round_edit($round_id) {
    log_assert(is_round_id($round_id));
    $round = round_get($round_id);
    return url_complex($round['page_name'], array('action' => 'edit'));
}

function url_round_edit_params($round_id) {
    log_assert(is_round_id($round_id));
    return url_complex("admin/runda/" . $round_id,
                       array('action' => 'edit-params'));
}

function url_round_edit_task_order($round_id) {
    log_assert(is_round_id($round_id));
    return url_complex("admin/runda/" . $round_id,
                       array('action' => 'edit-task-order'));
}

function url_round_create() {
    return url_complex("admin/runda-noua");
}

function url_round_register($round_id) {
    log_assert(is_round_id($round_id));
    return url_complex("inregistrare-runda/$round_id");
}

function url_round_register_view($round_id) {
    log_assert(is_round_id($round_id));
    return url_complex("lista-inregistrare/$round_id");
}

function url_round_delete($round_id) {
    log_assert(is_round_id($round_id));
    return url_complex("admin/runda/$round_id", array('action' => 'sterge-runda'));
}

// Job/monitor stuff.

function url_submit() {
    return url_complex("submit", array());
}

function url_monitor($filters = null) {
    if (is_null($filters)) {
        $filters = array();
    }
    return url_complex("monitor", $filters);
}

function url_job_detail($job_id) {
    log_assert(is_numeric($job_id));
    return url_complex("job_detail/".$job_id, array());
}

function url_job_view_source($job_id) {
    return url_complex("job_detail/".$job_id, array('action' => 'view-source'));
}

// Misc urls

function url_home() {
    return url_complex('', array());
}

function url_static($path) {
    return url_complex("static/$path", array());
}

function url_changes() {
    return url_complex("changes", array());
}

function url_changes_rss() {
    return url_complex("changes", array('format' => 'rss'));
}

function url_userwidget($user_id) {
    return url_complex("userwidget/".$user_id, array());
}

function url_admin() {
    return url_textblock('admin');
}

/**
 * User control.
 * @param int $user_id ID of user to ban or unban.
 * @return The URL of a page that, when accessed, will toggle the ban status.
 */
function url_user_control($user_id) {
    return url_complex('user_control', [ 'user_id' => $user_id ]);
}

function url_google_search() {
    return url_complex('search');
}
