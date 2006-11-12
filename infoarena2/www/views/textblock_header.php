<?php
// compute default permission prefix
//
// When displaying wiki page actions, we have to check for permissions.
// The same view code is used to display static wiki pages, news, tasks.
// task-view, wiki-view, round-view etc. 
$perm_prefix = getattr($view, 'perm_prefix', 'wiki');
?>

<div id="wikiOps">
    <ul>
        <?php if (identity_can($perm_prefix . '-edit', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'edit')) ?>">Editeaza</a></li>
        <?php } ?>
        <?php if (identity_can($perm_prefix . '-history', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'history')) ?>">Vezi istoria</a></li>
        <?php } ?>
        <?php if (identity_can($perm_prefix . '-delete', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'delete')) ?>">Sterge</a></li>
        <?php } ?>
        <?php if (identity_can('attach-create', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach')) ?>">Ataseaza</a></li>
        <?php } ?>
        <?php if (identity_can('attach-list', $textblock)) { ?>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach-list')) ?>">Listeaza atasamente</a></li>
        <?php } ?>
     </ul>
     <a class="feed" href="<?= htmlentities(url($view['page_name'], array('action' => 'feed'))) ?>" title="RSS Istoria paginii">
         RSS Istoria paginii <?= htmlentities($view['page_name']) ?>
     </a>
</div>
