<?php

require_once(IA_ROOT_DIR.'www/wiki/wiki.php');

// site header
include('header.php');

// wiki page header (actions)
include('textblock_header.php');

// blog sidebar
echo '<div class="blog-sidebar">';
wiki_include(IA_BLOG_SIDEBAR);
echo '</div>';

// revision warning
if (getattr($view, 'revision')) {
    include('revision_warning.php');
}

// blog content
echo '<div class="wiki_text_block">';
echo '<div class="blog">';
echo wiki_process_textblock($textblock);
echo '<div class="strap">';
echo '<strong>Categorii: </strong>';
foreach ($tags as $tag) {
    echo format_link(url_blog($tag['tag_name']), $tag['tag_name'], true).' ';
}
echo '<br/>';
echo 'Creat la '.htmlentities($first_textblock['creation_timestamp']).'de '.format_user_link($first_textblock["user_name"], $first_textblock["user_fullname"]);
echo '</div>';
echo '</div>';
echo '</div>';

// site footer
include('footer.php');

?>
