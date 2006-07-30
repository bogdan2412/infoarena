<?php

//
// Task displayer.
//

include('header.php');

?>
    <ul id="wikiOps">
        <li><a href="<?= url($textblock['name'], array('action' => 'edit')) ?>">Editeaza</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'history')) ?>">Vezi istoria</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach')) ?>">Ataseaza</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'attach-list')) ?>">Listeaza atasamente</a></li>
        <li><a href="<?= url($textblock['name'], array('action' => 'delete')) ?>">Sterge</a></li>
    </ul>

<?php
echo '<h1>' . htmlentities($textblock['title']) . '</h1>';
echo '<div class="wiki_text_block">';
echo wiki_process_text($textblock['text'], $view);
echo '</div>';

include('footer.php');

?>
