<?php
require_once(IA_ROOT_DIR.'www/format/pager.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/wiki/wiki.php');

// site header
include('header.php');

echo '<link rel="alternate" type="application/rss+xml" title="Blog infoarena" href="'.url_blog_feed().'" />';

// blog sidebar
echo '<div class="blog-sidebar">';
wiki_include(IA_BLOG_SIDEBAR);
echo '</div>';

echo '<div class="blog">';
echo '<h1>Blog infoarena</h1>';
// Include each blog post
foreach ($subpages as $subpage) {
    echo '<div class="item">';
    echo '<span class="date">';
    echo format_user_link($subpage["user_name"], $subpage["user_fullname"]).' &#8226; ';
    echo htmlentities(date('d M Y', strtotime($subpage['creation_timestamp'])));
    echo '</span>';

    $url = url_textblock($subpage['name']);
    $text = wiki_process_textblock_recursive($subpage);
    // Hijack title if already there.
    if (preg_match('/^\s*<h1>(.*)<\/h1>(.*)$/sxi', $text, $matches)) {
        echo '<h1>'.format_link($url, $matches[1], false).'</h1>';
        $text = $matches[2];
    } else {
        echo '<h1>'.format_link($url, $subpage['title']).'</h1>';
    }
    echo "<div class=\"wiki_text_block\">$text</div>";

    // Also display tags
    echo '<div class="strap">';
    echo '<strong>Categorii: </strong>';
    foreach ($subpage['tags'] as $tag) {
        echo format_link(url_blog($tag['tag_name']), $tag['tag_name'], true).' ';
    }
    echo '</div></div>';
}

// Pager at the bottom
echo format_pager($options);
echo "</div>";

// site footer
include('footer.php');

?>
