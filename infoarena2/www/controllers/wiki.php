<?php

// View a wiki page.
function controller_wiki_view($page_name) {
    // Tee hee.
    // If the page is missing jump to the edit/create controller.
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('wiki-view', $page);
    }
    else {
        controller_wiki_edit($page_name);
    }

    $view = array();
    
    // Viewer. Nicest thing in the world.
    $view['wikipage'] = $page;
    $view['title'] = $page['title'];
    execute_view_die('views/wikiview.php', $view);
}

// Edit a wiki page.
function controller_wiki_edit($page_name) {
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('wiki-edit', $page);
    }
    else {
        identity_require('wiki-create');
    }

    $view = array();
    $form_errors = array();

    if (!$page) {
        $page_title = $page_name;
        $page_content = "Scrie aici despre " . $page_name;
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

// Save controller. Create submits this. This submits itself.
function controller_wiki_save($page_name) {
    $page = textblock_get_revision($page_name);
    global $identity_user;

    if ($page) {
        identity_require('wiki-edit', $page);
    }
    else {
        identity_require('wiki-create');
    }

    // Validate data here and place stuff in errors.
    $form_errors = array();
    $view = array();

    $page_content = getattr($_POST, 'content', "");
    $page_title = getattr($_POST, 'title', "");
    if (strlen($page_content) < 1) {
        $form_errors['content'] = "Continutul paginii este prea scurt.";
    }
    if (strlen($page_title) < 1) {
        $form_errors['title'] = "Titlul este prea scurt.";
    }
    if (!$form_errors) {
        textblock_add_revision($page_name, $page_title, $page_content,
                               getattr($identity_user, 'id'));
        flash('Am actualizat continutul');
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
?>
