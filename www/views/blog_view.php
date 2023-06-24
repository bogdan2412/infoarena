<?php

require_once(IA_ROOT_DIR.'www/macros/macro_smfcomments.php');

// site header
include(CUSTOM_THEME . 'header.php');

// wiki page header (actions)
include('textblock_header.php');

// blog sidebar
echo '<div class="blog-sidebar">';
Wiki::include(IA_BLOG_SIDEBAR);
echo '</div>';

// revision warning
if (getattr($view, 'revision')) {
    include('revision_warning.php');
}

// blog content
echo '<div class="wiki_text_block">';
echo '<div class="blog">';
$text = Wiki::processTextblock($textblock);
echo hijack_title($text, null, $textblock['title']);
echo format_blogpost_author($first_textblock, $textblock['security'] != 'private');

echo $text;
echo '<div class="strap">';
echo '<strong>Categorii: </strong>';
foreach ($tags as $tag) {
    echo format_link(url_blog($tag['name']), $tag['name'], true).' ';
}
echo '</div>';
// blog comments
if (getattr($view, 'forum_topic')) {
    echo macro_smfcomments(array('topic_id' => $view['forum_topic'], 'display' => 'show'));
}
echo '</div>';
echo '</div>';

// site footer
include('footer.php');

?>
