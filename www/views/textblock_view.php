<?php

require_once(IA_ROOT_DIR.'www/wiki/wiki.php');

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
echo wiki_process_textblock($textblock);
echo '</div>';

// page comments
echo '<div id="comentarii">';
if (getattr($view, 'forum_topic')) {
    require_once(IA_ROOT_DIR.'www/macros/macro_remotebox.php');
    echo macro_remotebox(array('url' => IA_SMF_URL.'/ia_comments.php?topic_id='.$view['forum_topic']), true);
}
echo '</div>';

// site footer
include('footer.php');

?>
