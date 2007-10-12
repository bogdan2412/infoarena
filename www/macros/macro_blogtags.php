<?php

require_once(IA_ROOT_DIR . "common/db/blog.php");

function macro_blogtags($args) {
    $blog_tags = blog_get_tags();
    $html = '<ul class="blog-list">';
    foreach ($blog_tags as $tag) {
        $html .= '<li>';
        $html .= format_link(url_blog($tag['name']), $tag['name']);
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

?>
