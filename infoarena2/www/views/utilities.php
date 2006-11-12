<?php

// Check the big view variable for consistency.
function check_view($view)
{
    // Checking $view.
    log_print_r($view);

    log_assert(is_array($view));
    log_assert(is_string($view['title']));
    if (isset($view['form_errors']) || isset($view['form_values'])) {
        log_assert(is_array($view['form_errors']));
        log_assert(is_array($view['form_values']));
    }
    log_assert(!isset($view['wikipage']));
    if (isset($view['textblock'])) {
        log_assert(is_string($view['page_name']));
        log_assert(is_array($view['textblock']));
        log_assert(array_key_exists('name', $view['textblock']));
        log_assert(array_key_exists('title', $view['textblock']));
        log_assert(array_key_exists('text', $view['textblock']));
        log_assert(array_key_exists('timestamp', $view['textblock']));
    }
    if (isset($view['task'])) {
        log_assert(is_array($view['task']));
        log_assert(is_array($view['task_parameters']));
        //.. more here.
    }
}

function fval($paramName, $escapeHtml = true) {
    global $view;

    if ($escapeHtml) {
        return htmlentities(getattr($view['form_values'], $paramName));
    }
    else {
        return getattr($view['form_values'], $paramName);
    }
}

function ferr_span($paramName, $escapeHtml = true) {
    $error = ferr($paramName, $escapeHtml);

    if ($error) {
        return '<span class="fieldError">' . $error . '</span>';
    }
    else {
        return null;
    }
}

function ferr($paramName, $escapeHtml = true) {
    global $view;

    if ($escapeHtml) {
        return htmlentities(getattr($view['form_errors'], $paramName));
    }
    else {
        return getattr($view['form_errors'], $paramName);
    }
}

// Format a tiny link to an user.
// FIXME: proper styling
function format_user_link($user_name, $user_fullname) {
    $user_url = url("user/".$user_name);
    return "<a href=\"$user_url\">$user_fullname</a>";
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_tiny($user_name, $user_fullname) {
    $user_url = url("user/".$user_name);
    $avatar_url = image_resize_url("user/".$user_name, "avatar", "16x16");
   
    $result = "";
    $result .= "<div class=\"tiny-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= "<img width=\"16\" height=\"16\" src=\"$avatar_url\" />";
    $result .= "<span class=\"fullname\">$user_fullname</span> ";
    $result .= "<span class=\"username\">($user_name)</span> ";
    $result .= "</p></a></div>";

    return $result;
}

// Format a tiny user link, with a 32x32 avatar.
// FIXME: proper styling
function format_user_normal($user_name, $user_fullname) {
    $user_url = url("user/".$user_name);
    $avatar_url = image_resize_url("user/".$user_name, "avatar", "32x32");
   
    $result = "";
    $result .= "<div class=\"normal-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= "<img width=\"32\" height=\"32\" src=\"$avatar_url\" />";
    $result .= "<span class=\"fullname\">$user_fullname</span> <br />";
    $result .= "<span class=\"username\">$user_name</span> ";
    $result .= "</a></div>";

    return $result;
}

?>
