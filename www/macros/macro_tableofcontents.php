<?php

require_once(Config::ROOT . "common/db/textblock.php");
require_once(Config::ROOT . "www/format/format.php");

function macro_tableofcontents($args)
{
    $prefix = getattr($args, 'prefix', '');

    $subpages = textblock_get_by_prefix($prefix, false, false);

    $res = '<div class="macro-toc">';
    $res .= "<p>Table of contents for ".html_escape($prefix)."</p><ul>";
    for ($i = 0; $i < count($subpages); ++$i) {
        $title = $subpages[$i]['title'];
        $link = url_textblock($subpages[$i]['name']);
        $res .= "<li>".format_link($link, $title)."</li>";
    }

    $res .= "</ul></div>";
    return $res;
}

?>
