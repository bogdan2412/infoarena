<?php

// Format an array of xml attributes.
// Return '' or 'k1="v1" k2="v2"'.
// Escapes values, checks keys.
function format_attribs($attribs = array())
{
    log_assert(is_array($attribs), 'You must pass an array');

    $result = "";
    foreach ($attribs as $k => $v) {
        log_assert(preg_match("/[a-z][a-z_0-9]*/", $k), "Invalid attrib '$k'");
        if ($result == "") {
            $result .= "$k=\"".htmlentities($v)."\"";
        } else {
            $result .= " $k=\"".htmlentities($v)."\"";
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

    return "<$tag " . format_attribs($attribs) . '>';
}

// Format a html tag.
// Tag is a tag name(img, th, etc).
//
// Attrib values are escaped. Content is NOT escaped.
// Tag and attrib keys are checked.
function format_tag($tag, $attribs = array(), $content = null)
{
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), "Invalid tag '$tag'");

    if (is_null($content)) {
        return "<$tag ".format_attribs($attribs)." />";
    } else {
        return "<$tag ".format_attribs($attribs).">$content</$tag>";
    }
}

// Build a simple href
// By default escapes url & content
//
// You can set escape_content to false.
function format_link($url, $content, $escape_content = true) {
    if ($escape_content) {
        $content = htmlentities($content);
    }
    return format_tag("a", array("href" => $url), $content);
}

// Format img tag.
// NOTE: html says alt is REQUIRED.
// Escapes both args.
function format_img($src, $alt) {
    return format_tag("img", array("src" => $src, "alt" => $alt));
}

// Format avatar img.
function format_user_avatar($user_name, $width = 50, $height = 50)
{
    log_assert(is_whole_number($width), "Invalid width");
    log_assert(is_whole_number($height), "Invalid height");
    return format_tag("img", array(
            "src" => url_user_avatar($user_name, "{$width}x{$height}"),
            "alt" => ":)",
    ));
}

// Format a tiny link to an user.
// FIXME: proper styling
function format_user_link($user_name, $user_fullname) {
    return format_link(url_user_profile($user_name), $user_fullname);
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_tiny($user_name, $user_fullname) {
    $user_url = htmlentities(url_user_profile($user_name));
    $user_fullname = htmlentities($user_fullname);

    $result = "";
    $result .= "<div class=\"tiny-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= format_user_avatar($user_name, 16, 16);
    $result .= "<span class=\"fullname\">$user_fullname</span> ";
    $result .= "<span class=\"username\">($user_name)</span> ";
    $result .= "</a></div>";

    return $result;
}

// Format a tiny user link, with a 32x32 avatar.
// FIXME: proper styling
function format_user_normal($user_name, $user_fullname) {
    $user_url = htmlentities(url_user_profile($user_name));
    $user_fullname = htmlentities($user_fullname);

    $result = "";
    $result .= "<div class=\"normal-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= format_user_avatar($user_name, 32, 32);
    $result .= "<span class=\"fullname\">$user_fullname</span> <br />";
    $result .= "<span class=\"username\">$user_name</span> ";
    $result .= "</a></div>";

    return $result;
}

?>
