<div class="ad">
<?php
$sidebar = textblock_get_revision(IA_SIDEBAR_PAGE);
if ($sidebar) {
    echo '<div class="wiki_text_block">';
    echo Wiki::processTextblock($sidebar);
    echo '</div>';
}
?>
</div>
