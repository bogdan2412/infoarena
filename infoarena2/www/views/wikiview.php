<?php

//
// Wiki page displayer.
//

include('header.php');

$page_name = $view['page_name'];
?>
    <ul id="wikiops">
        <li><a href="<?= url($page_name, array('action' => 'edit')) ?>">Editeaza</a></li>
        <li><a href="<?= url($page_name, array('action' => 'history')) ?>">Vezi istoria</a></li>
        <li><a href="<?= url($page_name, array('action' => 'attach')) ?>">Ataseaza</a></li>
        <li><a href="<?= url($page_name, array('action' => 'listattach')) ?>">Listeaza atasamente</a></li>
        <li><a href="<?= url($page_name, array('action' => 'delete')) ?>">Sterge</a></li>
    </ul>
<?php

echo wiki_process_text($view['content'], null);
#echo 'Last modification by ' . $view['last-editor'];

include('footer.php');

?>
