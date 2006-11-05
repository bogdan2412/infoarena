<?php

// View a plain textblock.
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
            $perm = textblock_get_permission('history', $page);
        } else {
            $perm = textblock_get_permission('view', $page);
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
    execute_view_die('views/wikiview.php', $view);
}

// Show a textblock diff.
function controller_textblock_diff_revision($page_name, $rev_num) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    $rev = textblock_get_revision($page_name, $rev_num);
    if ($page) {
        $perm = textblock_get_permission('history', $page);
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
        $perm = textblock_get_permission('restore', $page);
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

// Display revisions
function controller_textblock_history($page_name) {
    $page = textblock_get_revision($page_name, null, false, false);
    if ($page) {
        $perm = textblock_get_permission('history', $page);
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
    $view['page_list'] = textblock_get_revisions($page_name);
    $view['count'] = count($view['page_list']);
    $view['current'] = $page;
    $view['feed_link'] = url($view['page_name'], array('action' => 'feed'));
    execute_view_die('views/history.php', $view);
}

// give a RSS feed with the history of a textblock
function controller_textblock_feed($page_name) {
    $page = textblock_get_revision($page_name);
    if (!$page) {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }

    $page_list = textblock_get_revisions($page_name, true, true);
    $count = count($page_list);
    $history = textblock_get_permission('view', $page);

    $view = array();
    $view['channel']['title'] = 'info-arena: '.$page['title'];
    $view['channel']['link'] = url($page_name, array(), true);
    $view['channel']['description'] = $view['channel']['title'];
    if ($history) {
        $view['channel']['description'] .= ' ('.$count.' revizii)';
    }
    $view['channel']['language'] = 'ro-ro';
    $view['channel']['copyright'] = '&copy; 2006 - info-arena';

    $i = 0;
    $view['item'][$i]['title'] = 'Revizia curenta: '.
                                 (getattr($page, 'title') ? $page['title'] :
                                  'FARA TITLU');
    $view['item'][$i]['description'] = wiki_process_text($page['text']);
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
        $view['item'][$i]['description'] = wiki_process_text($v['text']);
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

// Edit a textblock
function controller_textblock_edit($page_name) {
    $page = textblock_get_revision($page_name);

    // permission check
    if ($page) {
        // request permission to edit textblock
        $perm = textblock_get_permission('edit', $page);
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa modificati aceasta pagina');
            redirect(url(''));
        }
    }
    else {
        textblock_create_model_first($page_name);
        $perm = textblock_get_permission('create', $page_name);
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa creati aceasta pagina');
            redirect(url(''));
        }
    }

    $view = array();
    $form_errors = array();

    if (!$page) {
        $page_title = $page_name;
        $page_content = "Scrie aici despre " . $page_name;
        $view['title'] = "Creare " . $page_name;
    }
    else {
        $page_title = $page['title'];
        $page_content = $page['text'];
        $view['title'] = "Editare " . $page_name;
    }

    // This is the creation action.
    $view['page_name'] = $page_name;
    $view['action'] = url($page_name, array('action' => 'save'));
    $view['form_values'] = array('content'=> $page_content,
                                 'title' => $page_title);
    $view['form_errors'] = $form_errors;
    list($view['page_class'], $view['page_id']) = textblock_split_name($page_name);
    execute_view_die("views/textblock_edit.php", $view);
}

// Save changes controller
function controller_textblock_save($page_name) {
    $page = textblock_get_revision($page_name);
    global $identity_user;

    // permission check
    if ($page) {
        // request permission to edit textblock
        $perm = textblock_get_permission('edit', $page);
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa modificati aceasta pagina');
            redirect(url(''));
        }
    }
    else {
        textblock_create_model_first($page_name);

        $perm = textblock_get_permission('create', $page_name);
        if (!$perm) {
            flash_error('Nu aveti permisiunea sa creati aceasta pagina');
            redirect(url(''));
        }
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
        execute_view_die("views/textblock_edit.php", $view);
    }
}

// Deny creation of some textblocks. Redirect user to task/round/user
// details screen. See explanation.
// NOTE: This function may not return.
//
// Some textblocks cannot be created from scratch:
//  task/xxx  user/xxx  round/xxx ...
//
// Instead, their associated model must be created first.
// Associated models are created using specific controllers.
function textblock_create_model_first($page_name) {
    list($page_class, $object_id) = textblock_split_name($page_name);
    if (TEXTBLOCK_TASK == $page_class) {
        // You cannot create a new task via the texblock editor
        //
        // The edit-details controller creates a new task object and its
        // associated textblock
        flash('Acest task nu exista. Te trimit sa il creezi.<br/>'
              .'Mai intai, introdu cateva informatii de baza.');
        redirect(url($page_name, array('action' => 'details')));
    }

    return true;
}

?>
