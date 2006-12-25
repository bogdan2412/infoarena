<?php

require(IA_ROOT."common/rating.php");

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
// Attrib values are escaped. Content is escaped by default.
// Tag and attrib keys are checked.
function format_tag($tag, $content = null, $attribs = array(), $escape = true) {
    log_assert(is_array($attribs), 'attribs is not an array');
    log_assert(preg_match("/[a-z][a-z0-9]*/", $tag), "Invalid tag '$tag'");

    if (is_null($content)) {
        return "<$tag ".format_attribs($attribs)." />";
    } else {
        if ($escape) {
            $content = htmlentities($content);
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
    return format_link(url_user_profile($user_name), $rbadge.$user_fullname,
                       true, $attr);
}

// Format a tiny user link, with a 16x16 avatar.
// FIXME: proper styling
function format_user_tiny($user_name, $user_fullname, $rating = null) {
    $user_url = htmlentities(url_user_profile($user_name));
    $user_fullname = htmlentities($user_fullname);

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
    $user_url = htmlentities(url_user_profile($user_name));
    $user_fullname = htmlentities($user_fullname);

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

// Formats user rating badge. Rating badges are displayed before username
// and indicate the user's rating.
function format_user_ratingbadge($username, $rating) {
    if ($rating) {
        $class = rating_group($rating);
        $rating = rating_scale($rating);
        $att = array(
            'title' => 'Rating '.htmlentities($username).': '.$rating,
            'class' => 'user_'.$class,
        );
        return format_link(url_user_rating($username), '&bull;', false, $att);
    }
    else {
        // un-rated users have no badge
        return '';
    }
}

?>
