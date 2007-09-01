<?php
// Show wiki operations.
// Only show operations the current user can do.

// Check view parameters.
require_once(IA_ROOT_DIR . "common/textblock.php");
log_assert_valid(textblock_validate($textblock));
 
?>
<div id="wikiOps">
    <ul>
<?php if (identity_can('textblock-edit', $textblock)) { ?>
<li><?= format_link_access(url_textblock_edit($textblock['name']), 'Editeaza', 'e') ?></li>
<?php } ?>
<?php if (identity_can('textblock-history', $textblock)) { ?>
<li><?= format_link_access(url_textblock_history($textblock['name']), 'Istoria', 'i') ?></li>
<?php } ?>
<?php if (identity_can('textblock-move', $textblock)) { ?>
<li><?= format_link_access(url_textblock_move($textblock['name']), 'Muta', 'u') ?></li>
<?php } ?>
<?php if (identity_can('textblock-delete', $textblock)) { ?>
<li><?= format_link_access(
    url_textblock_delete($textblock['name']),
    'Sterge', 's', array('onclick' =>
    "return confirm('Aceasta actiune este ireversibila! Doresti sa continui?')")) ?>
</li>
<?php } ?>
<?php if (identity_can('textblock-attach', $textblock)) { ?>
<li><?= format_link_access(url_attachment_new($textblock['name']), 'Ataseaza', 'a') ?></li>
<?php } ?>
<?php if (identity_can('textblock-list-attach', $textblock)) { ?>
<li><?= format_link_access(url_attachment_list($textblock['name']), 'Listeaza atasamente', 'l') ?></li>
<?php } ?>
     </ul>
</div>
