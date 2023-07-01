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

// site footer
include('footer.php');

?>
