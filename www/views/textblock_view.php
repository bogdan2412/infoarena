<?php

// site header
require_once 'header.php';

// wiki page header (actions)
require_once 'textblock_header.php';

// revision warning
if (getattr($view, 'revision')) {
  require_once 'revision_warning.php';
}

// textblock content
echo '<div class="wiki_text_block">';
echo Wiki::processTextblock($textblock);
echo '</div>';

// site footer
include('footer.php');

?>
