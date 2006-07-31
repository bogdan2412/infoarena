<?php

//
// Wiki page displayer.
//

include('header.php');

?>
    <ul id="wikiOps">
        <li><a href="<?= url($textblock['name'], array('action' => 'edit')) ?>">Editeaza</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'history')) ?>">Vezi istoria</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach')) ?>">Ataseaza</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach-list')) ?>">Listeaza atasamente</a></li>
     </ul>
     <a class="rss" href="<?= htmlentities(url($view['page_name'], array('action' => 'feed'))) ?>" title="RSS Istoria paginii">
         RSS Istoria paginii <?= htmlentities($view['page_name']) ?>
     </a>
     
<?php
echo '<h1>'.htmlentities($textblock['title']).'</h1>';
if (getattr($view, 'revision')) {
    echo "<em>Atentie, aceasta pagina nu este actuala (este varianta de la ".$textblock['timestamp'].")</em>";
}
echo '<div class="wiki_text_block">';
echo wiki_process_text($textblock['text'], $textblock_context);
echo '</div>';
#echo 'Modificat ultima data la ' . $view[''];

include('footer.php');

?>
