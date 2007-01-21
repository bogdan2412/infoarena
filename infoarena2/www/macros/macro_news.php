<?php

require_once(IA_ROOT . "common/db/db.php");
require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "www/format/format.php");

function macro_news($args) {
    $prefix = getattr($args, 'prefix');
    $args['display_entries'] = getattr($args, 'display_entries', 5);
    $args['param_prefix'] = 'news_';
    $options = pager_init_options($args);
    $subpages = news_get_range($options['first_entry'], $options['display_entries'], $prefix);
    $options['total_entries'] = news_count($prefix);

    $res = '<div class="news">';
    foreach ($subpages as $subpage) {
        $res .= '<div class="item">';
        $res .= '<span class="date">'.htmlentities(date('d M Y', strtotime($subpage['timestamp']))).'</span>';

        $url = url_textblock($subpage['name']);
        $text = wiki_process_text_recursive($subpage);
        // Hijack title if already there.
        if (preg_match('/^\s*<h1>(.*)<\/h1>(.*)$/sxi', $text, $matches)) {
            $res .= '<h1>'.format_link($url, $matches[1], false).'</h1>';
            $text = $matches[2];
        } else {
            $res .= '<h1>'.format_link($url, $subpage['title']).'</h1>';
        }
        $res .= "<div class=\"wiki_text_block\">$text</div>";
        $res .= '</div>';
    }
    $res .= format_pager($options);
    $res .= "</div>";
    return $res;
}

?>
