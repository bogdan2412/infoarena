<?php
require_once(IA_ROOT_DIR.'www/format/pager.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/wiki/wiki.php');

// site header
include(CUSTOM_THEME . 'header.php');

echo '<link rel="alternate" type="application/rss+xml" title="Blog infoarena" href="'.url_blog_feed().'">';

// blog sidebar
echo '<div class="blog-sidebar">';
Wiki::include(IA_BLOG_SIDEBAR);
echo '</div>';

echo '<div class="blog">';
echo '<h1>Blog infoarena</h1>';
// Include each blog post
foreach ($subpages as $subpage) {
    echo '<div class="item">';

    $url = url_textblock($subpage['name']);
    $text = wiki_process_textblock_recursive($subpage);
    // Hijack title if already there.
    if (preg_match('/^\s*<h1>(.*)<\/h1>(.*)$/sxi', $text, $matches)) {
        echo '<h1>'.format_link($url, $matches[1], false).'</h1>';
        $text = $matches[2];
    } else {
        echo '<h1>'.format_link($url, $subpage['title']).'</h1>';
    }

    // Blog author and social buttons
    echo format_blogpost_author($subpage);
    echo "<div class=\"wiki_text_block\">$text</div>";

    // Display comment link
    echo '<p style="text-align: right;">';
    echo '<img style="vertical-align: middle;" src="'.url_static('images/comment.png').'">';
    echo '&nbsp;<a href="'.url_textblock($subpage['name']).'#comentarii">Comentarii ('.$subpage['comment_count'].')</a>';
    echo '</p>';

    // Also display tags
    echo '<div class="strap">';
    echo '<strong>Categorii: </strong>';
    foreach ($subpage['tags'] as $tag) {
        echo format_link(url_blog($tag['name']), $tag['name'], true).' ';
    }
    echo '</div></div>';
}

// Pager at the bottom
echo format_pager($options);
echo "</div>";

// site footer
include('footer.php');

?>
