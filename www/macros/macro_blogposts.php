<?php

require_once(IA_ROOT_DIR . "common/db/blog.php");

function macro_blogposts($args) {
    $blog_posts = blog_get_range(null, 0, 10);
    $html = '<ul class="blog-list">';
    foreach ($blog_posts as $blog_post) {
        $html .= '<li>';
        $html .= format_link(url_textblock($blog_post['name']), $blog_post['title']);
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

?>
