<?php

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
