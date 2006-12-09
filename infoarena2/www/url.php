<?php

// Creates URLs to various parts of the infoarena website.
// Please avoid hard-coding URLs throughout the code. 


// Compute url.
// The params array contains http get parameter,
// it's formatted in the end result as a series
// of key1=value1&key2=value2.
//
// NOTE: Only use this function for urls.
// NOTE: don't add ?x=y stuff in document.
//
// If $absolute is true(default false) then the server will be
// included in the url.
function url($document = '', $args = array(), $absolute = false) {
    log_assert(false === strpos($document, '?'), 'Page name contains ?');
    log_assert(is_array($args), "Argument list must be an array");
    log_assert(!array_key_exists("page", $args), "Argument list contains page");

    $args['page'] = $document;
    return url_from_args($args, $absolute);
}

// Construct an URL from an argument list.
// These are the exact $args you will receive in $_GET
function url_from_args($args, $absolute = false)
{
    // First part.
    if ($absolute) {
        $url = IA_URL;
    } else {
        $url = IA_URL_PREFIX;
    }
    $url .= getattr($args, "page", "home");

    // Actual args.
    $first = true;
    foreach ($args as $k => $v) {
        if ($k != 'page') {
            $url .= ($first ? "?" : "&");
            $first = false;
            $url .= $k . '=' . urlencode($v);
        }
    }

    return $url;
}

// Get an url for an attachement
function url_attachment($page, $file, $absolute = false) {
    log_assert(is_page_name($page));
    log_assert(is_attachment_name($file));
    return url($page, array('action' => 'download', 'file' => $file), $absolute);
}

// URL to homepage
function url_home($absolute = false) {
    return url('', array(), $absolute);
}

// Get an url for a resized image.
function url_image_resize($page, $file, $resize, $absolute = false)
{
    if ($resize) {
        return url($page, array(
                'action' => 'download',
                'file' => $file,
                'resize' => $resize,
        ), $absolute);
    } else {
        return url_attachment($page, $file, $absolute);
    }
}

// Url to the login page
function url_login($absolute = false) {
    return url("login", array(), $absolute);
}

// Url to the submit page
function url_submit($absolute = false) {
    return url("submit", array(), $absolute);
}

function url_textblock($page_name, $absolute = false) {
    return url($page_name, array(), $absolute);
}

function url_textblock_revision($page_name, $rev, $absolute = false) {
    return url($page_name, array('revision' => $rev), $absolute);
}

function url_textblock_edit($page_name, $absolute = false) {
    return url($page_name, array('action' => 'edit'), $absolute);
}

function url_textblock_history($page_name, $absolute = false) {
    return url($page_name, array('action' => 'history'), $absolute);
}

function url_textblock_diff($page_name, $revfrom, $revto, $absolute = false) {
    $args = array(
            'action' => 'diff',
            'rev_from' => $revfrom,
            'rev_to' => $revto
    );
    return url($page_name, $args, $absolute);
}

function url_textblock_move($page_name, $absolute = false) {
    return url($page_name, array('action' => 'move'), $absolute);
}

function url_textblock_delete($page_name, $absolute = false) {
    return url($page_name, array('action' => 'delete'), $absolute);
}

function url_textblock_restore($page_name, $rev, $absolute = false) {
    $args = array(
            'action' => 'restore',
            'revision' => $rev,
    );
    return url($page_name, $args, $absolute);
}

function url_textblock_delete_revision($page_name, $rev, $absolute = false) {
    $args = array(
            'action' => 'delete-revision',
            'revision' => $rev,
    );
    return url($page_name, $args, $absolute);
}

// Url to user profile page
// FIXME: DELETE THIS function and make sure no one uses it
function url_user_info($username, $absolute = false) {
    return url('userinfo/' . $username, array(), $absolute);
}

// Url to user profile page
function url_user_profile($username, $absolute = false) {
    return url(TB_USER_PREFIX . $username, array(), $absolute);
}

// Url to user profile :: rating evolution
function url_user_rating($username, $absolute = false) {
    return url(TB_USER_PREFIX . $username, array('action' => 'rating'),
               $absolute);
}

// Url to user profile :: statistics
function url_user_stats($username, $absolute = false) {
    return url(TB_USER_PREFIX . $username, array('action' => 'stats'),
               $absolute);
}

function url_user_avatar($username, $resize = "50x50", $absolute = false) {
    return url_image_resize(TB_USER_PREFIX . $username, 'avatar', $resize, $absolute);
}

// Url to job detail page
function url_job_detail($job_id, $absolute = false) {
    log_assert(is_numeric($job_id));
    return url("job_detail/".$job_id, array(), $absolute);
}

// Url to job download
function url_job_download($job_id, $absolute = false) {
    return url("job_detail/".$job_id, array('action' => 'download'), $absolute);
}

// Url to task view
function url_task($task_id, $absolute = false) {
    return url(TB_TASK_PREFIX . $task_id, array(), $absolute);
}

// Create link to unsubscribe user from mailing list
function url_unsubscribe($username, $key, $absolute = false) {
    log_assert($key);
    return url('unsubscribe/'.$username, array('c' => $key), $absolute);
}

// Create link to password reset controller
function url_resetpass($username, $absolute = false) {
    return url('resetpass/'.$username, array(), $absolute);
}

// Create link to confirm password reset
function url_resetpass_confirm($username, $key, $absolute = false) {
    log_assert($key);
    return url('confirm/'.$username, array('c' => $key), $absolute);
}


?>
