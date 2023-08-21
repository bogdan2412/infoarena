<?php

require_once(Config::ROOT."common/db/user.php");
require_once(Config::ROOT."common/user.php");
require_once(Config::ROOT."common/rating.php");
require_once(Config::ROOT."www/url.php");
require_once(Config::ROOT."www/utilities.php");

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
// You can use format_tag with no content for an empty <...> tag.
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
        return "<$tag ".format_attribs($attribs).">";
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
    if ($url) {
        $attr['href'] = $url;
    }
    return format_tag("a", $content, $attr, $escape);
}

// Build a link which posts data to a page
function format_post_link($url, $content, $post_data = array(), $escape = true, $attr = array(), $accesskey = null) {
    log_assert(is_array($attr), '$attr is not an array');
    log_assert(is_array($post_data), '$post_data is not an array');

    $link_url = "javascript:PostData(" . json_encode($url) . ", " . json_encode($post_data) . ")";

    if (is_null($accesskey)) {
        $link = format_link($link_url, $content, $escape, $attr);
    } else {
        $link = format_link_access($link_url, $content, $accesskey, $attr);
    }

    // Display a little "check" button beside the link if
    // javascript is disabled, by using a form with hidden fields.
    $form_content = '<input type="submit" style="margin: 0; padding: 0" value="&#10003;">';
    foreach ($post_data as $key => $value) {
        $form_content .= '<input type="hidden" name="' . html_escape($key) . '" value="' . html_escape($value) . '">';
    }
    $form_attr = array("class" => "inline_form",
                       "method" => "post",
                       "action" => $url,
                      );

    return $link . "<noscript>" . format_tag("form", $form_content, $form_attr, false) . "</noscript>";
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

function format_open_tests_img(): string {
  $url = url_static('images/open_small.png');
  $alt = 'open book';
  $attr = [
    'class' => 'open_task',
    'title' => 'teste publice',
  ];
  $html = format_img($url, $alt, $attr);
  return $html;
}

// Format avatar img.
function format_user_avatar($user_name, $size_type = "full",
                            $absolute = false)
{
    log_assert(is_valid_size_type($size_type), "Invalid size type");
    $url = url_user_avatar($user_name, $size_type);
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
        $rating_group = rating_group($rating);
        $attr = array('class' => 'user_'.$rating_group["group"]);
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
    $result .= "<span class=\"tiny-user\">";
    $result .= format_link($user_url,
                           format_user_avatar($user_name, "tiny", false).$user_fullname,
                           false);
    $result .= ' '.$rbadge;
    $result .= "<span class=\"username\">"
               .format_link($user_url, $user_name)
               ."</span>";
    $result .= "</span>";

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
                           format_user_avatar($user_name, "small", false),
                           false);
    $result .= "<span class=\"fullname\">$user_fullname</span><br>";
    $result .= $rbadge;
    $result .= "<span class=\"username\">"
               .format_link($user_url, $user_name)
               ."</span>";
    $result .= "</div>";

    return $result;
}

// Return rating group and colour based on user's sclaed rating scale.
// Rating groups (from highest to lowest ranking): 1, 2, 3, 4, 0
// NOTE: It outputs 0 when user is not rated
function rating_group($rating, $is_admin = false) {
    if ($is_admin) {
        // all mighty admin - black
        return array("group" => 5, "colour" => "#000000");
    }
    if (!$rating) {
        // user unrated - white
        return array("group" => 0, "colour" => "#ffffff");
    }
    if ($rating < 540) {
        // green
        return array("group" => 4, "colour" => "#00a900");
    }
    else if ($rating < 600) {
        // blue
        return array("group" => 3, "colour" => "#0000ff");
    }
    else if ($rating < 700) {
        // yellow
        return array("group" => 2, "colour" => "#ddcc00");
    }
    else {
        // red
        return array("group" => 1, "colour" => "#ee0000");
    }
}

// Formats user rating badge. Rating badges are displayed before username
// and indicate the user's rating.
function format_user_ratingbadge($username, $rating) {
    if ($rating) {
        $is_admin = user_is_admin(user_get_by_username($username));
        $rating = rating_scale($rating);
        $rating_group = rating_group($rating, $is_admin);
        $class = $rating_group["group"];
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

    if (is_null($format)) {
        $format = Config::DATE_FORMAT;
    }

    $timeZone = new DateTimeZone(Config::TIMEZONE);
    $dt = new DateTime('@' . $timestamp);
    $dt->setTimeZone($timeZone);
    $res = IntlDateFormatter::formatObject($dt, $format, 'ro_RO.utf8');
    return $res;
}

/**
 * Formats the task author tags
 *
 * @param array $authors    an array containing the author tags
 * @return array
 */
function format_task_author_tags($authors) {
    if ($authors == null)
      return array();

    log_assert(is_array($authors));

    $authors_formatted = array();
    foreach ($authors as $tag) {
      $authors_formatted[] =
          format_link(
              url_task_search(array($tag["id"])), $tag["name"]);
    }

    return $authors_formatted;
}

/**
 * Formats a small box containing the name of the filter(with category) and a
 * link for removing the filter on browsers not supporting javascript
 *
 * @param string $tag_name
 * @return string
 */
function format_selected_task_filter($tag) {
    return
        '<div class="selected-filter">' . '#' . html_escape($tag['name']) .
        ($tag['parent_name'] ? '(' . html_escape($tag['parent_name']) . ')' :
        '') . '</div>';
}

/**
 * Format's a link for adding or removing a tag on task filtering
 *
 * @param array $tag
 * @param array $tag_ids
 * @param bool $has_subtags_selected
 * @return string
 */
function format_task_filter_tag($tag, $tag_ids) {
    log_assert(is_array($tag), 'tag must be an array');
    log_assert(is_array($tag_ids), 'tag_ids must be an array');

    if ($tag['id']) {
        if (in_array($tag['id'], $tag_ids)) {
            $link_tags = array_diff($tag_ids, array($tag['id']));
        } else {
            // written like this just for similarity
            $link_tags = array_merge($tag_ids, array($tag['id']));
        }
    } else {
        $link_tags = $tag_ids;
    }

    // If we press a category we automatically deselect all subfilters
    // This has a nice property, we can deselect all authors with the same
    // capital
    $bad_tags = array();
    foreach (getattr($tag, 'sub_tags', array()) as $subtag) {
        $bad_tags[] = $subtag['id'];
    }
    // If we press a filter we deselect its parent
    if (isset($tag['parent'])) {
        $bad_tags[] = $tag['parent'] ;
    }
    // for categories like A-E, so not clicked tags are not regarded as bad
    // tags
    $bad_tags = array_intersect($bad_tags, $link_tags);
    $link_tags = array_diff($link_tags, $bad_tags);

    // if we can't calculate the number of tasks after clicking this tag don't
    // do it
    if (getattr($tag, 'nocount')) {
        $tag['task_count'] = null;
    }

    if (count($bad_tags) > 0 || $tag['id']) {
        $link = url_task_search($link_tags);
    } else {
        $link = '';
    }

    return format_link($link,
                     $tag['name'] .
                     (getattr($tag, 'task_count') ?
                          '(' . $tag['task_count'] . ')' : ''));
}

/**
 * Formats a task tag drag-and-drop menu
 *
 * @param array $tags
 * @param array $selected_tags
 * @return string
 */
function format_task_tag_menu($tags, $selected_tags) {
    log_assert(is_array($tags), 'tags should be an array');
    log_assert(is_array($selected_tags), 'selected_tags should be an array');
    $menu = '<ul class="mainmenu">';

    foreach ($tags as $tag) {
        $classes = array();

        $menu .= '<li class="' . implode(' ', $tag['classes']) . '">';

        $menu .= format_task_filter_tag($tag, $selected_tags);

        // we get the subtags, there must be one because we can't pick
        // categories as task tags
        $menu .= '<ul class="submenu">';
        foreach ($tag['sub_tags'] as $subtag) {
            $menu .= '<li class="' . implode(' ', $subtag['classes']) . '">';
            $menu .= format_task_filter_tag($subtag, $selected_tags);
            $menu .= '</li>';
        }
        $menu .= '</ul>';
        $menu .= '</li>';
    }
    $menu .= '</ul>';
    return $menu;
}

/**
 * Formats an acm-round table cell which contains a task score
 *
 * @param int $score
 * @param int $penalty
 * @param int $submission
 * @return string
 */
function format_acm_score($score, $penalty, $submission) {
    if ($submission == 0) {
        return "0";
    }

    $penalty -= ($submission - 1) * 20;
    $result = '<span style="font-size: 18px;text-weight: bold;color: ';
    if ($score > 0) {
        $result .= 'green">+';
        if ($submission > 1)
            $result .= ($submission - 1);
    } else {
        $result .= 'red">-' . $submission;
    }
    $result .= '</span><br>' . $penalty;
    return $result;
}
