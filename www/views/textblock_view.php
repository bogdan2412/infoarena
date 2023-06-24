<?php

// site header
include(CUSTOM_THEME . 'header.php');

// wiki page header (actions)
include('textblock_header.php');

// revision warning
if (getattr($view, 'revision')) {
    include('revision_warning.php');
}

// textblock content
echo '<div class="wiki_text_block">';
echo Wiki::processTextblock($textblock);
echo '</div>';

// page comments
if (getattr($view, 'forum_topic')) {
    require_once(IA_ROOT_DIR.'www/macros/macro_smfcomments.php');
    echo macro_smfcomments(array('topic_id' => $view['forum_topic'], 'display' => 'hide'));
}

// site footer
include('footer.php');

?>
