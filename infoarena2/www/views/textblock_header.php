<?php
// Show wiki operations.
// Only show operations the current user can do.
?>

<div id="wikiOps">
    <ul>
        <?php if (identity_can('textblock-edit', $textblock)) { ?>
        <li><?= format_link(url($textblock['name'], array('action' => 'edit')), 'Editeaza') ?></li>
        <?php } ?>
        <?php if (identity_can('textblock-history', $textblock)) { ?>
        <li><?= format_link(url($textblock['name'], array('action' => 'history')), 'Vezi istoria') ?></li>
        <?php } ?>
        <?php if (identity_can('textblock-move', $textblock)) { ?>
        <li><?= format_link(url($textblock['name'], array('action' => 'move')), 'Muta') ?></li>
        <?php } ?>
        <?php if (identity_can('textblock-delete', $textblock)) { ?>
        <li>

<a href="<?= htmlentities(url($textblock['name'], array('action' => 'delete'))) ?>" onclick="return confirm('Aceasta actiune este ireversibila! Doresti sa continui?')">Sterge</a>

        </li>
        <?php } ?>
        <?php if (identity_can('textblock-attach', $textblock)) { ?>
        <li><?= format_link(url($textblock['name'], array('action' => 'attach')), 'Ataseaza fisier') ?></li>
        <?php } ?>
        <?php if (identity_can('textblock-list-attach', $textblock)) { ?>
        <li><?= format_link(url($textblock['name'], array('action' => 'attach-list')), 'Listeaza atasamente') ?></li>
        <?php } ?>
     </ul>
</div>
