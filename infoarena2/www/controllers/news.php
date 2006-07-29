<?php
require_once("controllers/wiki.php");

//this should not be here
define('IA_MAX_NEWS', 3);

function controller_news_view_all() {
    // start view
    $view = array();
    $view['title'] = 'Arhiva stiri';
    $view['page_name'] = 'news';

    // feed the troll
    $pagenum = request('pagenum', 0);

    if (0 > $pagenum) {
        $pagenum = 0;
    }
    
    $view['page'] = $pagenum;
    $view['news'] = news_get_range($pagenum * IA_MAX_NEWS, IA_MAX_NEWS);
    if (!$view['news']) {
        flash_error('Pagina de stiri nu exista');
        redirect(url('news'));
    }
    else {
        execute_view_die("views/news.php", $view);
    }
}

function controller_news_edit($page_name) {
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('news-edit', $page);
    }
    else {
        identity_require('news-create');
    }

    $view = array();
    $form_errors = array();

    if (!$page) {
        $page_title = $page_name;
        $page_content = "Stire despre " . $page_name;
    }
    else {
        $page_title = $page['title'];
        $page_content = $page['text'];
    }

    // This is the creation action.
    $view['title'] = "Creare " . $page_name;
    $view['action'] = url($page_name, array('action' => 'save'));
    $view['form_values'] = array('content'=> $page_content,
                                 'title' => $page_title);
    $view['form_errors'] = $form_errors;
    execute_view_die("views/wikiedit.php", $view);
}

function controller_news_save($page_name) {
    $page = textblock_get_revision($page_name);
    global $identity_user;

    if ($page) {
        identity_require('news-edit', $page);
    }
    else {
        identity_require('news-create');
    }

    // Validate data here and place stuff in errors.
    $form_errors = array();
    $view = array();

    $page_content = getattr($_POST, 'content', "");
    $page_title = getattr($_POST, 'title', "");
    if (strlen($page_content) < 1) {
        $form_errors['content'] = "Stirea este prea scurta.";
    }
    if (strlen($page_title) < 1) {
        $form_errors['title'] = "Titlul este prea scurt.";
    }
    if (!$form_errors) {
        textblock_add_revision($page_name, $page_title, $page_content,
                               getattr($identity_user, 'id'));
        flash('Am actualizat continutul stirii');
        redirect(url($page_name));
    }
    else {
        $view['title'] = "Editare " . $page_name;
        $view['action'] = url($page_name, array('action' => 'save'));
        $form_values['content'] = $page_content;
        $view['form_values'] = array('content'=> $page_content,
                                     'title' => $page_title);
        $view['form_errors'] = $form_errors;
        execute_view_die("views/wikiedit.php", $view);
    }
}

// View a news page (a single news article).
function controller_news_view($page_name, $rev_num = null) {
    // Tee hee.
    // If the page is missing jump to the edit/create controller.
    $page = textblock_get_revision($page_name, $rev_num);
    if ($page) {
        identity_require('news-view', $page);
        if ($rev_num) identity_require('history', $page);
    }
    else
    if ($rev_num) {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }
    else {
        controller_news_edit($page_name);
    }

    $view = array();
    
    // Viewer. Nicest thing in the world.
    $view['revision'] = $rev_num;
    $view['wikipage'] = $page;
    $view['title'] = $page['title'];
    execute_view_die('views/wikiview.php', $view);
}

?>
