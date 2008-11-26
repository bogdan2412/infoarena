<?php

require_once(IA_ROOT_DIR . "common/db/textblock.php");
require_once(IA_ROOT_DIR . "common/cache.php");
require_once(IA_ROOT_DIR . "common/external_libs/simple_html_dom.php");

// Hijacks title from $text if already there. If $url is null the title
// will not have a link.
function hijack_title(&$text, $url, $title) {
    if (preg_match('/^\s*<h1>(.*)<\/h1>(.*)$/sxi', $text, $matches)) {
        $text = $matches[2];
        if (is_null($url)) {
            return '<h1>'.$matches[1].'</h1>';
        } else {
            return '<h1>'.format_link($url, $matches[1], false).'</h1>';
        }
    } else {
        if (is_null($url)) {
            return '<h1>'.html_escape($title).'</h1>';
        } else {
            return '<h1>'.format_link($url, $title).'</h1>';
        }
    }
}

// Returns a snippet of a textblock. The snippet does not contain images
// (if $remove_images = true) and it doesn't exceed $max_num_words.
//
// Some exceptions:
//    * news -- they are fully rendered in the snippet if $whole_news is true
//    * blog posts having a huge first paragraph -- although we exceed the
//        number of words allowed we should have a snippet for this kind of
//        posts after all
function get_snippet($tb, $max_num_words, $whole_news = false,
        $remove_images = true) {
    $cache_id = 'snip_' . preg_replace('/[^a-z0-9\.\-_]/i', '_',
                $tb['name']) . '_' . $max_num_words . '_' . $whole_news .
                $remove_images . '_' . db_date_parse($tb['timestamp']);
    $cache_res = disk_cache_get($cache_id);

    if ($cache_res == false) {
        $url = url_textblock($tb['name']);
        $html_text = wiki_process_textblock_recursive($tb);

        $cache_res .= hijack_title($html_text, $url, $tb['title']);
        $cache_res .= '<div class="wiki_text_block">';

        // May be there is a better way to find out if $tb is tagged
        // 'stiri'. On the other hand, this operation is cached and we
        // shouldn't worry too much about performance.
        if ($whole_news && tag_exists('textblock', $tb['name'], tag_get_id('stiri'))) {
            // Don't compute snippet for news -- they should be rendered as
            // they are in the snippet.
            $cache_res .= $html_text;
        } else {
            $html_dom = str_get_html($html_text);
            $num_words = 0;

            if ($remove_images) {
                // Remove all the images -- ussually they look bad in a snippet.
                foreach ($html_dom->find('img') as $element) {
                    $element->outertext = '';
                }
            }

            // Select some paragraphs so that the maximum number of words is
            // not exceeded and at least one paragraph is selected.
            foreach ($html_dom->find('p') as $element) {
                $par_words = count(explode(" ", $element->plaintext));
                if ($num_words + $par_words >= $max_num_words && $num_words > 0) {
                    break;
                }
                $num_words += $par_words;
                $cache_res .= $element;
            }

            $cache_res .= '<a href="' . html_escape($url) .
                '"> &raquo; Citeste restul insemnarii</a>';
            unset($html_dom);
        }

        $cache_res .= '</div>';
        disk_cache_set($cache_id, $cache_res, 3);
    }

    return $cache_res;
}

// Check if textblock security string is valid
// FIXME: check task/round existence?
function is_textblock_security_descriptor($descriptor)
{
    return preg_match("/^ \s* task: \s* (".IA_RE_TASK_ID.") \s* $/xi", $descriptor) ||
           preg_match("/^ \s* round: \s* (".IA_RE_ROUND_ID.") \s* $/xi", $descriptor) ||
           preg_match('/^ \s* (private|protected|public) \s* $/xi', $descriptor);
}

// Validates a textblock.
// NOTE: this might be incomplete, so don't rely on it exclusively
function textblock_validate($tb) {
    $errors = array();

    // FIXME How to handle this?
    log_assert(is_array($tb), "You didn't even pass an array");

    if (!is_normal_page_name(getattr($tb, 'name', ''))) {
        $errors['name'] = 'Nume de pagina invalid.';
    }

    // FIXME: move this in textblock edit controller
    if (strlen(getattr($tb, 'title', '')) < 1) {
        $errors['title'] = 'Titlu prea scurt.';
    }

    // FIXME: move this in textblock edit controller
    if (strlen(getattr($tb, 'title', '')) > 64) {
        $errors['title'] = 'Titlu prea lung.';
    }

    if (!is_user_id(getattr($tb, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid';
    }

    if (!is_null(getattr($tb, 'forum_topic')) && !is_whole_number(getattr($tb, 'forum_topic'))) {
        $errors['forum_topic'] = 'Topic forum invalid';
    }

    // NOTE: missing timestamp is OK!!!
    // It stands for 'current moment'.
    if (!is_db_date(getattr($tb, 'timestamp', db_date_format()))) {
        $errors['timestamp'] = 'Timestamp invalid.';
    }

    if (!is_db_date(getattr($tb, 'creation_timestamp', db_date_format()))) {
        $errors['creation_timestamp'] = 'Timestamp invalid.';
    }

    if (!is_textblock_security_descriptor(getattr($tb, 'security'))) {
        $errors['security'] = "Descriptor de securitate gresit.";
    }

    return $errors;
}

// This function copies all starting with $srcprefix and copies the over to
// $destprefix.
// It also does template-replacing for everything in $replace, if non-null.
// You can also change the security descriptor on all those files.
//
// Use this like textblock_copy_replace('template/newtask', 'problema/capsuni');
function textblock_copy_replace($srcprefix, $dstprefix, $replace, $security, $user_id)
{
    assert($srcprefix != $dstprefix);
    assert(is_textblock_security_descriptor($security));
    assert(is_whole_number($user_id));

    $textblocks = textblock_get_by_prefix($srcprefix, true, false);
    foreach ($textblocks as $textblock) {
        if ($replace !== null) {
            textblock_template_replace($textblock, $replace);
        }
        if ($replace !== null) {
            $textblock['security'] = $security;
        }
        $textblock['name'] = preg_replace('/^'.preg_quote($srcprefix, '/').'/i', $dstprefix, $textblock['name']);

        //FIXME: hack to keep creation_timestamp correct when textblock already exists
        $first_textblock = textblock_get_revision($textblock['name']);
        if (!$first_textblock) {
            $first_textblock['creation_timestamp'] = null;
        }

        textblock_add_revision($textblock['name'], $textblock['title'],
                $textblock['text'], $user_id, $textblock['security'],
                $textblock['forum_topic'], null,
                $first_textblock['creation_timestamp']);
    }
}

// Does template replacing in a textblock.
// Replaces all occurences of %key% with value, for all key, value pairs
// in the $replace array.
//
// You should mainly use this horrible painful hack on templates.
//
// MODIFIES $textblock
//
// FIXME: optimize.
function textblock_template_replace(&$textblock, $replace)
{
    foreach ($replace as $key => $value) {
        $textblock['title'] = str_replace("%$key%", $value, $textblock['title']);
        $textblock['text'] = str_replace("%$key%", $value, $textblock['text']);
    }
}

?>
