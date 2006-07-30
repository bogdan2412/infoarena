<?php

function macro_tableofcontents($args)
{
    $prefix = getattr($args, 'prefix', $args['context']['page_name']);

    $subpages = textblock_get_names_with_user($prefix);

    $res = '<div class="macro-toc" style="float:right">';
    $res .= "<p>Table of contents for $prefix</p><ul>";
    for ($i = 0; $i < count($subpages); ++$i) {
        $title = $subpages[$i]['title'];
        $link = url($subpages[$i]['name']);
        $res .= "<li><a href=\"$link\">$title</a></li>";
    }

    $res .= "</ul></div>";
    return $res;
}

?>
