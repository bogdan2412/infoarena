<?php

require_once(IA_ROOT_DIR.'www/config.php');

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
// If $absolute is true(default false) then IA_URL_HOST will be included in
// the url.
function url_complex($document = '', $args = array(), $absolute = false) {
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

// Makes an url absolute. It just prepends IA_URL_HOST
function url_absolute($url)
{
    log_assert(strpos($url, 'http') !== 0, "Url begins with http");
    return IA_URL_HOST . $url;
}

// Construct an URL from an argument list.
// These are the exact $args you will receive in $_GET
function url_from_args($args)
{
    // First part.
    $url = IA_URL_PREFIX . getattr($args, "page", "home");

    // Actual args.
    $first = true;
    foreach ($args as $k => $v) {
        if (is_null($v)) {
            continue;
        }
        if ($k != 'page') {
            $url .= ($first ? "?" : "&");
            $first = false;
            $url .= $k . '=' . urlencode($v);
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

function url_blog_feed() {
    return url_complex('blog', array('action' => 'rss'));
}

function url_blog($tag = null) {
    if ($tag) {
        return url_complex('blog', array('tag' => $tag));
    }
    return url_complex('blog');
}

function url_blog_admin($blog_post) {
    log_assert(is_blog_post($blog_post));
    return url_complex("admin/blog/$blog_post");
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
            'revision' => $rev,
    ));
}

// Textblock attachments:

function url_attachment($page, $file) {
    log_assert(is_page_name($page));
    log_assert(is_attachment_name($file));
    return url_complex($page, array(
            'action' => 'download',
            'file' => $file,
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

function url_image_resize($page, $file, $resize)
{
    if ($resize) {
        return url_complex($page, array(
                'action' => 'download',
                'file' => $file,
                'resize' => $resize,
        ));
    } else {
        return url_attachment($page, $file);
    }
}

// User stuff

function url_login() {
    return url_complex("login");
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
    return url_complex("register");
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
    return url_complex(IA_USER_TEXTBLOCK_PREFIX . $username, array());
}

function url_user_rating($username) {
    return url_complex(IA_USER_TEXTBLOCK_PREFIX . $username, array(
            'action' => 'rating'
    ));
}

function url_user_stats($username) {
    return url_complex(IA_USER_TEXTBLOCK_PREFIX . $username, array(
            'action' => 'stats'
    ));
}

function url_user_avatar($username, $resize = "50x50") {
    return url_image_resize(IA_USER_TEXTBLOCK_PREFIX . $username, 'avatar', $resize);
}

function url_unsubscribe($username, $key) {
    log_assert($key);
    return url_complex('unsubscribe/'.$username, array('c' => $key));
}

// Task/round stuff.

function url_task($task_id) {
    log_assert(is_task_id($task_id));
    return url_complex("problema/$task_id");
}

function url_task_edit($task_id) {
    log_assert(is_task_id($task_id));
    return url_complex("admin/problema/$task_id");
}

function url_task_create() {
    return url_complex("admin/problema-noua");
}

function url_round_edit($round_id) {
    log_assert(is_round_id($round_id));
    return url_complex("admin/runda/$round_id");
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

function url_reeval($filters = null) {
    if (is_null($filters)) {
        $filters = array();
    }
    return url_complex("reeval", $filters);
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

function url_forum() {
    return IA_SMF_URL;
}

?>
