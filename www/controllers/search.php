<?php

function controller_search() {
    $view = array();
    $view['title'] = 'Rezultatele cautarii';
    execute_view_die('views/google_search_results.php', $view);
}
