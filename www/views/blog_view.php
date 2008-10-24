<?php

require_once(IA_ROOT_DIR.'www/wiki/wiki.php');
require_once(IA_ROOT_DIR.'www/macros/macro_remotebox.php');

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
echo '<h1>'.$textblock['title'].'</h1>';
echo wiki_process_textblock($textblock);
echo '<div class="strap">';
echo '<strong>Categorii: </strong>';
foreach ($tags as $tag) {
    echo format_link(url_blog($tag['tag_name']), $tag['tag_name'], true).' ';
}
echo '<br/>';
echo 'Creat la '.html_escape($first_textblock['creation_timestamp']).' de '.format_user_link($first_textblock["user_name"], $first_textblock["user_fullname"]);
echo '</div>';
// blog comments
echo '<div id="comentarii">';
if (getattr($view, 'topic_id')) {
    echo macro_remotebox(array('url' => IA_SMF_URL.'/ia_comments.php?topic_id='.$view['topic_id']), true);
}
echo '</div></div>';
echo '</div>';

// site footer
include('footer.php');

?>
