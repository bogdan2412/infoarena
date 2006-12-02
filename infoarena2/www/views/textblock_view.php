<?php

require_once(IA_ROOT.'www/wiki/wiki.php');

// RSS discovery
$view['head'] = '<link rel="alternate" href="' . url($page_name, array('action' => 'feed')) . '" title="RSS Revizii ' . $textblock['title'] . '" type="application/rss+xml" />';

// site header
include('header.php');

// wiki page header (actions)
include('textblock_header.php');

// revision warning
if (getattr($view, 'revision')) {
    echo "<em>Atentie, aceasta pagina nu este actuala (este varianta de la ".$textblock['timestamp'].")</em>";
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
