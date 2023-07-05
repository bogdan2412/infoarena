<?php

require_once 'header.php';

require_once 'textblock_header.php';

if (getattr($view, 'revision')) {
  require_once 'revision_warning.php';
}

echo '<div class="wiki_text_block">';
echo Wiki::processTextblock($textblock);
echo '</div>';

include('footer.php');

?>
