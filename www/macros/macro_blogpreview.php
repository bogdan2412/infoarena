<?php

require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "common/db/blog.php");
require_once(IA_ROOT_DIR . "common/textblock.php");

function macro_blogpreview($args) {
    // The reason why we set $max_num_words "by hand" here and not via a macro
    // argument is this: every time we change it new snippet cache entries
    // are created for the posts included in the blog preview. If the number
    // of posts is big this could very well lead to cache bloating.
    //
    // The problem would be partially fixed if the cache would
    // implement entry expiring.
    $max_num_words = 200;
    $prefix = getattr($args, 'prefix');
    $args['display_entries'] = getattr($args, 'display_entries', 1);
    $args['param_prefix'] = 'blogpreview_';
    $subpages = blog_get_range(null, 0, $args['display_entries']);

    $res = '<div class="news">';
    foreach ($subpages as $subpage) {
        $res .= '<div class="item">';
        $res .= '<span class="date">' .
            html_escape(date('d M Y', strtotime($subpage['creation_timestamp']))) .
            '</span>';
        $res .= get_snippet($subpage, $max_num_words, true);
        $res .= '</div>';
    }
    $res .= "</div>";
    return $res;
}

?>
