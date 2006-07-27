<?php

//
// Wiki page displayer.
//

include('header.php');

?>
    <ul id="wikiops">
        <li><a href="<?= url('edit/' . $view['wikipage']) ?>">Editeaza</a></li>
        <li><a href="<?= url('history/' . $view['wikipage']) ?>">Vezi istoria</a></li>
        <li><a href="<?= url('attachment/create' . $view['wikipage']) ?>">Ataseaza</a></li>
        <li><a href="<?= url('delete/' . $view['wikipage']) ?>">Sterge</a></li>
    </ul>
<?php

echo '<h3 id="wikititle">View Generic page <i>'.$view[wikipage]."</i></h1>";
echo wiki_process_text($view['wikitext'], null);
echo 'Last modification by ' . $view['last-editor'];

include('footer.php');

?>
