<?php

require_once(IA_ROOT . "common/db/textblock.php");

function macro_tableofcontents($args)
{
    $prefix = getattr($args, 'prefix', '');

    $subpages = textblock_get_by_prefix($prefix, false, false);

    $res = '<div class="macro-toc">';
    $res .= "<p>Table of contents for ".htmlentities($prefix)."</p><ul>";
    for ($i = 0; $i < count($subpages); ++$i) {
        $title = $subpages[$i]['title'];
        $link = url($subpages[$i]['name']);
        $res .= "<li><a href=\"".htmlentities($link)."\">".htmlentities($title)."</a></li>";
    }

    $res .= "</ul></div>";
    return $res;
}

?>
