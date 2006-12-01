<?php

// Format a tiny link to an user.
// FIXME: proper styling
function format_user_link($user_name, $user_fullname) {
    return href(url_user_profile($user_name), $user_fullname);
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_tiny($user_name, $user_fullname) {
    $user_url = htmlentities(url_user_profile($user_name));
    $avatar_url = url_user_avatar($user_name, "16x16");

    $user_fullname = htmlentities($user_fullname);

    $result = "";
    $result .= "<div class=\"tiny-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= img($avatar_url, $user_name);
    $result .= "<span class=\"fullname\">$user_fullname</span> ";
    $result .= "<span class=\"username\">($user_name)</span> ";
    $result .= "</a></div>";

    return $result;
}

// Format a tiny user link, with a 32x32 avatar.
// FIXME: proper styling
function format_user_normal($user_name, $user_fullname) {
    $user_url = htmlentities(url_user_profile($user_name));
    $avatar_url = url_user_avatar($user_name, "32x32");

    $user_fullname = htmlentities($user_fullname);

    $result = "";
    $result .= "<div class=\"normal-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= img($avatar_url, $user_name);
    $result .= "<span class=\"fullname\">$user_fullname</span> <br />";
    $result .= "<span class=\"username\">$user_name</span> ";
    $result .= "</a></div>";

    return $result;
}

// Format a html tag.
// Tag is a tag name(img, th, etc).
// args is an array with xml attributes. values are escaped.
// Content is the tag content. It is NOT escaped, it's free html.
function format_tag($tag, $args = array(), $content = "")
{
    log_assert(is_array($args), '$args not an array');
    log_assert(is_string($tag), '$tag is not a string');
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), 'Invalid tag "'.$tag.'"');
    $result = "<$tag";
    foreach ($args as $k => $v) {
        log_assert(preg_match("/[a-z][a-z_0-9]*/", $k), 'Invalid attrib "'.$k.'"');
        $result .= " $k=\"".htmlentities($v)."\"";
    }
    $result .= ">$content</$tag>";

    return $result;
}

?>
