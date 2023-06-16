<?php

function controller_search() {
    $view = array();
    $view['title'] = 'Rezultatele căutării';
    execute_view_die('views/google_search_results.php', $view);
}
