<?php

// Format a tiny link to an user.
// FIXME: proper styling
function format_user_link($user_name, $user_fullname) {
    $user_url = url(TB_USER_PREFIX.$user_name);
    $user_fullname = htmlentities($user_fullname);
    return "<a href=\"$user_url\">$user_fullname</a>";
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_tiny($user_name, $user_fullname) {
    $pagename = TB_USER_PREFIX.$user_name;
    $user_url = url($pagename);
    $avatar_url = image_resize_url($pagename, "avatar", "16x16");

    $user_fullname = htmlentities($user_fullname);

    $result = "";
    $result .= "<div class=\"tiny-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= "<img width=\"16\" height=\"16\" src=\"$avatar_url\" alt=\"$user_name\"/>";
    $result .= "<span class=\"fullname\">$user_fullname</span> ";
    $result .= "<span class=\"username\">($user_name)</span> ";
    $result .= "</a></div>";

    return $result;
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_normal($user_name, $user_fullname) {
    $pagename = TB_USER_PREFIX.$user_name;
    $user_url = url($pagename);
    $avatar_url = image_resize_url($pagename, "avatar", "16x16");

    $user_fullname = htmlentities($user_fullname);

    $result = "";
    $result .= "<div class=\"normal-user\">";
    $result .= "<a href=\"$user_url\">";
    $result .= "<img src=\"$avatar_url\" />";
    $result .= "<span class=\"fullname\">$user_fullname</span> <br />";
    $result .= "<span class=\"username\">$user_name</span> ";
    $result .= "</a></div>";

    return $result;
}

// Quickly format a link to a certain page.
// $url should really come from the url function.
function format_link($text, $url)
{
    return '<a href="'.$url.'">'.htmlentities($text).'</a>';
}

?>
