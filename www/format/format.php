<?php

require_once(IA_ROOT_DIR."common/db/user.php");
require_once(IA_ROOT_DIR."common/rating.php");
require_once(IA_ROOT_DIR."www/url.php");
require_once(IA_ROOT_DIR."www/utilities.php");

// Format an array of xml attributes.
// Return '' or 'k1="v1" k2="v2"'.
// Escapes values, checks keys.
function format_attribs($attribs = array())
{
    log_assert(is_array($attribs), 'You must pass an array');

    $result = "";
    foreach ($attribs as $k => $v) {
        if (is_null($v))
            continue;

        log_assert(preg_match("/[a-z][a-z_0-9]*/", $k), "Invalid attrib '$k'");
        if ($result == "") {
            $result .= "$k=\"".html_escape($v)."\"";
        } else {
            $result .= " $k=\"".html_escape($v)."\"";
        }
    }

    return $result;
}

// Format an open html tag:
// <tag k1="v1" k2="v2" .. >
// You have to manually close the tag somehow.
// You can use format_tag with no content for an empty <... /> tag.
function format_open_tag($tag, $attribs = array())
{
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), "Invalid tag '$tag'");

    return "<$tag " . format_attribs($attribs) . ">";
}

// Format a html tag.
// Tag is a tag name(img, th, etc).
//
// Attrib values are escaped. Content is escaped by default.
// Tag and attrib keys are checked.
function format_tag($tag, $content = null, $attribs = array(), $escape = true) {
    log_assert(is_array($attribs), 'attribs is not an array');
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), "Invalid tag '$tag'");

    if (is_null($content)) {
        return "<$tag ".format_attribs($attribs)." />";
    } else {
        if ($escape) {
            $content = html_escape($content);
        }
        return "<$tag ".format_attribs($attribs).">$content</$tag>";
    }
}

// Build a simple href
// By default escapes url & content
//
// You can set escape_content to false.
function format_link($url, $content, $escape = true, $attr = array()) {
    log_assert(is_array($attr), '$attr is not an array');
    $attr['href'] = $url;
    return format_tag("a", $content, $attr, $escape);
}

// Highlight an access key in a string, by surrounding the first occurence
// of the $key with <span class="access-key"></span>
// Case insensitive, nothing happens if $key is not found.
// FIXME: Improve this logic.
function format_highlight_access_key($string, $key) {
    if (($pos = stripos($string, $key)) !== false) {
        return substr_replace($string,
                '<span class="access-key">'.$string[$pos].'</span>', $pos, 1);
    } else {
        return $string;
    }
}

// Format a link with an access key.
// Html content not supported because of format_highlight_access_key.
function format_link_access($url, $content, $key, $attr = array()) {
    $attr['accesskey'] = $key;
    $content = format_highlight_access_key(html_escape($content), $key);
    return format_link($url, $content, false, $attr);
}

// Format img tag.
// NOTE: html says alt is REQUIRED.
// Escapes both args.
function format_img($src, $alt, $attr = array()) {
    $attr['src'] = $src;
    $attr['alt'] = $alt;
    return format_tag("img", null, $attr);
}

// Format avatar img.
function format_user_avatar($user_name, $width = 50, $height = 50,
                            $absolute = false)
{
    log_assert(is_whole_number($width), "Invalid width");
    log_assert(is_whole_number($height), "Invalid height");
    $url = url_user_avatar($user_name, "L{$width}x{$height}");
    if ($absolute) {
        $url = url_absolute($url);
    }
    return format_img($url, $user_name);
}

// Format a tiny link to an user.
// FIXME: proper styling
function format_user_link($user_name, $user_fullname, $rating = null) {
    if (is_null($rating)) {
        $attr = array();
    } else {
        $attr = array('class' => 'user_'.rating_group($rating));
    }

    $rbadge = format_user_ratingbadge($user_name, $rating);
    return $rbadge.format_link(url_user_profile($user_name), $user_fullname, false, $attr);
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_tiny($user_name, $user_fullname, $rating = null) {
    $user_url = html_escape(url_user_profile($user_name));
    $user_fullname = html_escape($user_fullname);

    $rbadge = format_user_ratingbadge($user_name, $rating);

    $result = "";
    $result .= "<div class=\"tiny-user\">";
    $result .= format_link($user_url,
                           format_user_avatar($user_name, 16, 16, false).$user_fullname,
                           false);
    $result .= ' '.$rbadge;
    $result .= "<span class=\"username\">"
               .format_link($user_url, $user_name)
               ."</span> ";
    $result .= "</div>";

    return $result;
}

// Format a tiny user link, with a 32x32 avatar.
// FIXME: proper styling
function format_user_normal($user_name, $user_fullname, $rating = null) {
    $user_url = html_escape(url_user_profile($user_name));
    $user_fullname = html_escape($user_fullname);

    $rbadge = format_user_ratingbadge($user_name, $rating);

    $result = "";
    $result .= "<div class=\"normal-user\">";
    $result .= format_link($user_url,
                           format_user_avatar($user_name, 32, 32, false),
                           false);
    $result .= "<span class=\"fullname\">$user_fullname</span><br />";
    $result .= $rbadge;
    $result .= "<span class=\"username\">"
               .format_link($user_url, $user_name)
               ."</span> ";
    $result .= "</div>";

    return $result;
}

// Return rating group based on user's absolute rating.
// Rating groups (from highest to lowest ranking): 1, 2, 3, 0
// NOTE: It outputs 0 when user is not rated
function rating_group($absolute_rating, $is_admin = false) {
    if ($is_admin) {
        // all mighty admin
        return 4;
    }
    if (!$absolute_rating) {
        return 0;
    }
    $rating = rating_scale($absolute_rating);
    if ($rating < 520) {
        // green
        return 3;
    }
    else if ($rating < 600) {
        // yellow
        return 2;
    }
    else {
        // red
        return 1;
    }
}

// Formats user rating badge. Rating badges are displayed before username
// and indicate the user's rating.
function format_user_ratingbadge($username, $rating) {
    if ($rating) {
        $class = rating_group($rating);
        $rating = rating_scale($rating);
        $att = array(
            'title' => 'Rating '.html_escape($username).': '.$rating,
            'class' => 'rating-badge-'.$class,
        );
        return format_link(url_user_rating($username), '&bull;', false, $att);
    }
    else {
        // un-rated users have no badge
        return '';
    }
}

// Format a date for display.
// Can take *both* unix timestamps and utc strings(db_date stuff).
//
// FIXME: user timezone, user format, etc.
// global identityUser;
//
// HTML safe(don't pass through html_escape.)
function format_date($date, $format = null) {
    if (is_db_date($date)) {
        $timestamp = db_date_parse($date);
    } elseif (is_whole_number($date)) {
        $timestamp = $date;
    } elseif (is_null($date)) {
        $timestamp = time();
    } else {
        log_error("Invalid date argument");
    }

    // Romanian locale. This is very usefull for dates, etc.
    // FIXME: only set this in format_date, etc?
    if (!setlocale(LC_TIME, "ro_RO.utf8")) {
        log_warn("Romanian locale missing, this tends to suck for formatting");
    }

    // FIXME: user prefs.
    $timezone = IA_DATE_DEFAULT_TIMEZONE;
    if (is_null($format)) {
        $format = IA_DATE_DEFAULT_FORMAT;
    }

    // PHP 5.1+
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set($timezone);
        $res = strftime($format, $timestamp);
        date_default_timezone_set('UTC');
    } else {
        // Probably won't work, whatever.
        $res = strftime($format, $timestamp);
    }
    return $res;
}

?>
