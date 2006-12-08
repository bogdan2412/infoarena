<?php

require_once(IA_ROOT.'www/wiki/wiki.php');
require_once(IA_ROOT.'www/format/format.php');
require_once(IA_ROOT.'www/format/list.php');

// RSS discovery
if ('view' == $action) {
    $view['head'] = '<link rel="alternate" href="' . htmlentities(url($page_name, array('action' => 'feed'))) . '" title="RSS Revizii ' . htmlentities($textblock['title']) . '" type="application/rss+xml" />';
}

// site header
include('header.php');

// display user info accross all user profile pages
echo wiki_include($template_userheader, array('user' => $user['username']));

// show profile tabs
$options = array(
    'view' => format_link(url($page_name), 'Pagina personala'),
    'rating' => format_link(url($page_name, array('action' => 'rating')), 'Evolutie rating'),
    'stats' => format_link(url($page_name, array('action' => 'stats')), 'Statistici'),
);
// mark selected action with class 'active'
$options[$action] = array($options[$action], array('class' => 'active'));
echo format_ul($options, 'htabs');

if ('view' == $action) {
    // showing user's personal page

    // wiki page header (actions)
    include('textblock_header.php');

    // revision warning
    // FIXME: duplicated code (see views/textblock_view.php)
    if (getattr($view, 'revision')) {
	include('revision_warning.php');
    }

    echo '<div class="wiki_text_block">';
    echo wiki_process_text(getattr($textblock, 'text'));
    echo '</div>';
}
else {
    // showing ratings / statistics
    echo wiki_include($template, array('user' => $user['username']));
}

// site footer
include('footer.php');

?>
