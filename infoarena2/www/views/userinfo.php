<?php

include('header.php');

echo '<div class="wiki_text_block">';
echo wiki_process_text(getattr($textblock, 'text'));
echo '</div>';

include('footer.php');

?>
