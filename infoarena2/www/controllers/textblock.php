<?php

// View a plain old textblock.
// That textblock can be owned by something else.
function controller_textblock_view($page_name, $rev_num = null) {
    // Templates can't be seen
    if (preg_match("/^template\//i", $page_name)) {
        flash("Template-urile pot fi vizualizate doar ".
                "prin incluziune, te trimit sa editezi.");
        redirect(url($page_name, array('action' => 'edit')));
    }

    // Get actual page.
    $page = textblock_get_revision($page_name, $rev_num);

    // If the page is missing jump to the edit/create controller.
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
        // Missing page.
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
    //$view['count'] = textblock_get_revision_count($page_name);
    $view['page_list'] = textblock_get_revisions_without_content($page_name);
    $view['count'] = count($view['page_list']);
    $view['current'] = $page;
    $view['feed_link'] = url($view['page_name'], array('action' => 'feed'));
    execute_view_die('views/history.php', $view);
}

// give a RSS feed with the history of a textblock
function controller_textblock_feed($page_name) {
    $page = textblock_get_revision_with_username($page_name);
    if (!$page) {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }

    $page_list = textblock_get_revisions_with_username($page_name);
    $count = count($page_list);
    $history = textblock_get_permission($page, 'view');

    $view = array();
    $view['channel']['title'] = 'info-arena: '.$page['title'];
    $view['channel']['link'] = url($page_name, array(), true);
    $view['channel']['description'] = $view['channel']['title'];
    if ($history) {
        $view['channel']['description'] .= ' ('.$count.' revizii)';
    }
    $view['channel']['language'] = 'ro-ro';
    $view['channel']['copyright'] = '&copy; 2006 -asociatia info-arena';

    $i = 0;
    $view['item'][$i]['title'] = 'Revizia curenta: '.
                                 (getattr($page, 'title') ? $page['title'] :
                                  'FARA TITLU');
    $context = array('page_name' => $page['name'], 'title' => $page['title']);
    $view['item'][$i]['description'] = wiki_process_text_recursive(
                                       $page['text'], $context);
    $view['item'][$i]['pubDate'] = date('r', strtotime($page['timestamp']));
    $view['item'][$i]['guid'] = sha1($page['name'].$page['timestamp']);
    $view['item'][$i]['link'] = url($page['name'], array(), true).
                               '#'.$view['item'][$i]['guid'];
    $view['item'][$i]['author'] = $page['username'];

    if (!$history) {
        execute_view_die('views/rss.php', $view);
    }

    $i = 1; 
    for($rev_num = $count-1; $rev_num >= 0; $rev_num--, $i++) {
        $v = $page_list[$rev_num];
        $view['item'][$i]['title'] = 'Revizia #'.($rev_num+1).': '.
                                     (getattr($v, 'title') ? $v['title'] :
                                     'FARA TITLU');
        $context = array('page_name' => $v['name'], 'title' => $v['title']);
        $view['item'][$i]['description'] = wiki_process_text_recursive(
                                           $v['text'], $context);
        $view['item'][$i]['pubDate'] = date('r', strtotime($v['timestamp']));
        $view['item'][$i]['guid'] = sha1($v['name'].$v['timestamp']);
        $view['item'][$i]['link'] = url($v['name'],
                                    array('revision' => $rev_num), true).
                                    '#'.$view['item'][$i]['guid'];
        $view['item'][$i]['author'] = $v['username'];
        if ($i == IA_MAX_FEED_ITEMS) {
            break;
        }
    }    

    execute_view_die('views/rss.php', $view);
}
?>
