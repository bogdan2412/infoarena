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

if (getattr($view, "forum_topic")) {
    require_once(IA_ROOT_DIR.'www/macros/macro_smftopic.php');
    echo '<div id="forum_box">';
    echo macro_smftopic(array('topic_id' => $forum_topic));
    echo '</div>';
}

// site footer
include('footer.php');

?>
