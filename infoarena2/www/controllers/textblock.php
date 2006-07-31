<?php

// View a plain old textblock.
// That textblock can be owned by something else.
function controller_textblock_view($page_name, $rev_num = null) {
    // Tee hee.
    // If the page is missing jump to the edit/create controller.
    $page = textblock_get_revision($page_name, $rev_num);
    if ($page) {
        if ($rev_num) {
            $perm = textblock_get_permission($page, 'history');
        } else {
            $perm = textblock_get_permission($page, 'view');
        }
        if (!$perm) {
            flash_error("Nu ai voie sa vezi aceasta pagina");
            redirect(url(''));
        }
    } else {
        // Missing page template here.
        flash_error("Nu am gasit pagina, te trimit sa editezi");
        redirect(url($page_name, array('action' => 'edit')));
    }

    // Build view.
    $view = array();
    $view['title'] = $page['title'];
    $view['revision'] = $rev_num;
    $view['page_name'] = $page_name;
    $view['textblock'] = $page;
    $view['textblock_context'] = textblock_get_context($page);
    execute_view_die('views/wikiview.php', $view);
}

// Show a textblock diff.
function controller_textblock_diff_revision($page_name, $rev_num) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    $rev = textblock_get_revision($page_name, $rev_num);
    if ($page) {
        $perm = textblock_get_permission($page, 'history');
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa accesati aceasta pagina.');
            redirect(url(''));
        }
    }
    else {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }
    if (is_null($rev_num)) {
        flash_error("Nu ati specificat revizia");
        redirect(url($page_name));
    }
    if (!$rev) {
        flash_error("Revizia nu exista!");
        redirect(url($page_name));
    }

    $diff_title = string_diff($rev['title'], $page['title']);
    $diff_content = string_diff($rev['text'], $page['text']);

    $view = array();
    $view['page_name'] = $page_name;
    $view['title'] = 'Diferente '.$page_name;
    $view['diff_title'] = explode("\n", $diff_title);
    $view['diff_content'] = explode("\n", $diff_content);
    execute_view_die('views/diff.php', $view);
}

// Restore a certain revision
function controller_textblock_restore_revision($page_name, $rev_num) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    $rev = textblock_get_revision($page_name, $rev_num);
    if ($page) {
        $perm = textblock_get_permission($page, 'restore');
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa executati aceasta actiune.');
            redirect(url(''));
        }
       
    else {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }
    if (is_null($rev_num)) {
        flash_error("Nu ati specificat revizia");
        redirect(url($page_name));
    }
    if (!$rev) {
        flash_error("Revizia nu exista!");
        redirect(url($page_name));
    }
    
    textblock_add_revision($rev['name'], $rev['title'], $rev['text'],
                           getattr($identity_user, 'id'));
    redirect(url($page_name));
}

// display revisions
function controller_textblock_history($page_name) {
    $page = textblock_get_revision_without_content($page_name);
    if ($page) {
        $perm = textblock_get_permission($page, 'history');
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa accesati aceasta pagina.');
            redirect(url(''));
        }
    }
    else {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }

    $view = array();
    $view['page_name'] = $page_name;
    $view['title'] = 'Istoria paginii '.$page_name;
    $view['count'] = textblock_get_revision_count($page_name);
    $view['page_list'] = textblock_get_revisions_without_content($page_name);
    $view['current'] = $page;
    execute_view_die('views/history.php', $view);
}
?>
