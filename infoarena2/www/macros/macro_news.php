<?php

require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "www/format/format.php");

function macro_news($args) {
    $prefix = getattr($args, 'prefix');
    $args['display_entries'] = getattr($args, 'display_entries', 5);
    $options = pager_init_options($args);
    $subpages = news_get_range($options['first_entry'], $options['display_entries'], $prefix);
    $options['total_entries'] = news_count($prefix);

    $res = '<div class="news">';
    foreach ($subpages as $subpage) {
        $res .= '<div class="item">';
        $res .= '<span class="date">'.htmlentities(date('d M Y', strtotime($subpage['timestamp']))).'</span>';

        $url = url($subpage['name']);
        $text = wiki_process_text_recursive(getattr($subpage, 'text'));
        // Hijack title if already there.
        if (preg_match('/^\s*<h1>(.*)<\/h1>(.*)$/sxi', $text, $matches)) {
            $res .= '<h1>'.href($url, $matches[1]).'</h1>';
            $text = $matches[2];
        } else {
            log_print($text);
            $res .= '<h1>'.href($url, $subpage['content']).'</h1>';
        }
        $res .= "<div class=\"wiki_text_block\">$text</div>";
        $res .= '</div>';
    }
    $res .= format_pager($options);
    $res .= "</div>";
    return $res;
}

?>
