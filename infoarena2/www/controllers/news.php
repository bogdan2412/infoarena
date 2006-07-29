<?php
require_once("controllers/wiki.php");

//this should not be here
define('IA_MAX_NEWS', 2);

function controller_news() {
    // start view
    $view['title'] = 'Arhiva stiri';
    $view['page_name'] = 'news';

    // feed the troll
    $pagenum = request('pagenum', 0);
    $view['page'] = $pagenum;
    $view['news'] = news_get_range($pagenum*IA_MAX_NEWS, IA_MAX_NEWS);
    if (!$view['news']) {
        flash_error('Pagina de stiri nu exista');
        redirect(url('news'));
    }
    else {
        execute_view_die("views/news.php", $view);
    }
}
?>