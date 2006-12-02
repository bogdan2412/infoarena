<?php

// RSS discovery
$view['head'] = '<link rel="alternate" href="' . url($page_name, array('action' => 'feed')) . '" title="RSS Revizii ' . $textblock['title'] . '" type="application/rss+xml" />';

include('header.php');
include('textblock_header.php');
require_once(IA_ROOT . 'www/wiki/wiki.php');

// Wiki pages should print their own title
// echo '<h1>'.htmlentities($textblock['title']).'</h1>';
if (getattr($view, 'revision')) {
    echo "<em>Atentie, aceasta pagina nu este actuala (este varianta de la ".$textblock['timestamp'].")</em>";
}
echo '<div class="wiki_text_block">';
log_print("PROCESSING");
print(wiki_process_text(getattr($textblock, 'text')));
log_print("NO MORE PROCESSING");
echo '</div>';

include('footer.php');

?>
