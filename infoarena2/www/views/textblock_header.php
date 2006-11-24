<?php
// Show wiki operations.
// Only show operations the current user can do.
?>

<div id="wikiOps">
    <ul>
        <?php if (identity_can('textblock-edit', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'edit')) ?>">Editeaza</a></li>
        <?php } ?>
        <?php if (identity_can('textblock-history', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'history')) ?>">Vezi istoria</a></li>
        <?php } ?>
        <?php if (identity_can('textblock-move', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'move')) ?>">Muta</a></li>
        <?php } ?>
        <?php if (identity_can('textblock-delete', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'delete')) ?>">Sterge</a></li>
        <?php } ?>
        <?php if (identity_can('textblock-attach', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach')) ?>">Ataseaza</a></li>
        <?php } ?>
        <?php if (identity_can('textblock-list-attach', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach-list')) ?>">Listeaza atasamente</a></li>
        <?php } ?>
     </ul>
</div>
