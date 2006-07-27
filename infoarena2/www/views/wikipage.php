<?php

//
// Wiki page displayer.
//

include('header.php');

if (!isset($view['wikipage'])) {
    echo '<div class="error">Error: no page name</div>';
}
else {
    echo wiki_process_page($view['wikipage'], $view);
}


include('footer.php');

?>
