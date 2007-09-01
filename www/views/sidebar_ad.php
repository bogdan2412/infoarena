<div class="ad">
<?php
$sidebar = textblock_get_revision(IA_SIDEBAR_PAGE);
if ($sidebar) {
    echo '<div class="wiki_text_block">';
    echo wiki_process_textblock($sidebar);
    echo '</div>';
}
?>
</div>
