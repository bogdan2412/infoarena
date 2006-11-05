<?php

function macro_news($args) {
    $prefix = getattr($args, 'prefix', null);
    $count = getattr($args, 'count', IA_MAX_NEWS);
    $subpages = news_get_range(0, $count, $prefix);

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
    $res .= "</div>";
    return $res;
}

?>
