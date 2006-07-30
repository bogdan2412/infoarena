<?php

function macro_news($args) {
    $prefix = getattr($args, 'prefix', null);
    $count = getattr($args, 'count', IA_MAX_NEWS);
    $subpages = news_get_range(0, $count, $prefix);
    $res = '<div class="news-toc"><ul class="list">';
    for ($i = 0; $i < $count; $i++) {
        $title = $subpages[$i]['title'];
        $link = url($subpages[$i]['name']);
        $res .= "<li><a href=\"$link\">$title</a></li>";
    }
    $res .= "</ul></div>";
    return $res;
}

?>
