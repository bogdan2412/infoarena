<?php

// RSS discovery
$view['head'] = '<link rel="alternate" href="' . url($page_name, array('action' => 'feed')) . '" title="RSS Revizii ' . $textblock['title'] . '" type="application/rss+xml" />';

// compute default permission prefix
//
// When displaying wiki page actions, we have to check for permissions.
// The same view code is used to display static wiki pages, news, tasks.
// task-view, wiki-view, round-view etc. 
$perm_prefix = getattr($view, 'perm_prefix', 'wiki');

include('header.php');

?>
    <div id="wikiOps">
        <ul>
            <?php if (identity_can($perm_prefix . '-edit', $textblock)) { ?>
            <li><a href="<?= url($textblock['name'], array('action' => 'edit')) ?>">Editeaza</a></li>
            <?php } ?>
            <?php if (identity_can($perm_prefix . '-history', $textblock)) { ?>
            <li><a href="<?= url($textblock['name'], array('action' => 'history')) ?>">Vezi istoria</a></li>
            <?php } ?>
            <?php if (identity_can('textblock-attach', $textblock)) { ?>
            <li><a href="<?= url($textblock['name'], array('action' => 'attach')) ?>">Ataseaza</a></li>
            <?php } ?>
            <?php if (identity_can('textblock-listattach', $textblock)) { ?>
            <li><a href="<?= url($textblock['name'], array('action' => 'attach-list')) ?>">Listeaza atasamente</a></li>
            <?php } ?>
         </ul>
         <a class="feed" href="<?= htmlentities(url($view['page_name'], array('action' => 'feed'))) ?>" title="RSS Istoria paginii">
             RSS Istoria paginii <?= htmlentities($view['page_name']) ?>
         </a>
    </div>
     
<?php
// Wiki pages should print their own title
// echo '<h1>'.htmlentities($textblock['title']).'</h1>';
if (getattr($view, 'revision')) {
    echo "<em>Atentie, aceasta pagina nu este actuala (este varianta de la ".$textblock['timestamp'].")</em>";
}
echo '<div class="wiki_text_block">';
echo textblock_get_html($textblock);
echo '</div>';
#echo 'Modificat ultima data la ' . $view[''];

include('footer.php');

?>
