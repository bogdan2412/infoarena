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
<?php
echo '<h1>'.htmlentities($textblock['title']).'</h1>';
if (!is_null($view['revision'])) {
    echo "<em>Atentie, aceasta pagina nu este actuala (este varianta de la ".$wikipage['timestamp'].")</em>";
}
echo '<div class="wiki_text_block">';
echo wiki_process_text($textblock['text'], $view);
echo '</div>';
#echo 'Modificat ultima data la ' . $view[''];

include('footer.php');

?>
