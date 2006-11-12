<?php

require_once("pager.php");

function macro_news($args) {
    $prefix = getattr($args, 'prefix');
    $args['display_entries'] = getattr($args, 'display_entries', 5);
    $options = pager_init_options($args);
    $subpages = news_get_range($options['first_entry'], $options['display_entries'], $prefix);
    $options['total_entries'] = news_count($prefix);

    $res = '<div class="news">';
    for ($i = 0; $i < count($subpages); $i++) {
        $res .= '<div class="item">';
        $title = $subpages[$i]['title'];
        $link = url($subpages[$i]['name']);
        $res .= '<span class="date">'.htmlentities(date('d M Y', strtotime($subpages[$i]['timestamp']))).'</span>';
        $res .= "<h3><a href=\"$link\">$title</a></h3>";
        $res .='<div class="wiki_text_block">';
        $res .= wiki_process_text_recursive(getattr($subpages[$i], 'text'));
        $res .= '</div></div>';
    }
    $res .= format_pager($options);
    $res .= "</div>";
    return $res;
}

?>
