<?php

require_once(IA_ROOT.'www/wiki/wiki.php');

// RSS discovery
$view['head'] = '<link rel="alternate" href="' . htmlentities(url($page_name, array('action' => 'feed'))) . '" title="RSS Revizii '.htmlentities($textblock['title']).'" type="application/rss+xml" />';

// site header
include('header.php');

// wiki page header (actions)
include('textblock_header.php');

// revision warning
if (getattr($view, 'revision')) {
    include('revision_warning.php');
}

// textblock content
echo '<div class="wiki_text_block">';
log_print("PROCESSING");
echo wiki_process_text(getattr($textblock, 'text'));
log_print("NO MORE PROCESSING");
echo '</div>';

// site footer
include('footer.php');

?>
