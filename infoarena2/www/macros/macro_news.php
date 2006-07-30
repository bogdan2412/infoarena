<?php

function macro_news($args) {
    $prefix = getattr($args, 'prefix', null);
    $count = getattr($args, 'count', IA_MAX_NEWS);
    $subpages = news_get_range(0, $count, $prefix);
    $res = '<div class="news">';
    for ($i = 0; $i < $count; $i++) {
        $res .= '<div class="item">';
        $title = $subpages[$i]['title'];
        $link = url($subpages[$i]['name']);
        $res .= "<h3><a href=\"$link\">$title</a></h3>";
        $res .= '<span class="date">'.htmlentities($subpages[$i]['timestamp']).'</span>';
        $res .='<div class="wiki_text_block">';
        $minicontext = array('page_name' => $subpages[$i]['name'], 'title' => $title);
        $res .= wiki_process_text(getattr($subpages[$i], 'text'), $minicontext);
        $res .= '</div></div>';
    }
    $res .= "</div>";
    return $res;
}

?>
