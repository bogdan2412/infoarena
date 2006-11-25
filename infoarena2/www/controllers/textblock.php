<?php

require_once(IA_ROOT . "www/format/pager.php");

// View a plain textblock.
// That textblock can be owned by something else.
function controller_textblock_view($page_name, $rev_num = null) {
    global $identity_user;

    // Get actual page.
    $crpage = textblock_get_revision($page_name);

    // If the page is missing jump to the edit/create controller.
    if ($crpage) {
        // FIXME: hack to properly display latest revision.
        // Checks if $rev_num is the latest.
        if ($rev_num && $rev_num != textblock_get_revision_count($page_name)) {
            identity_require("textblock-history", $crpage);
            $page = textblock_get_revision($page_name, $rev_num);

            if (!$page) {
                flash_error("Revizia ".htmlentities($rev_num)." nu exista.");
                $page = $crpage;
            }
        } else {
            identity_require("textblock-view", $crpage);
            $page = $crpage;
        }
    } else {
        // Missing page.
        // FIXME: what if the user can't create the page?
        flash_error("Nu am gasit pagina, te trimit sa editezi");
        redirect(url($page_name, array('action' => 'edit')));
    }

    // Build view.
    $view = array();
    $view['title'] = $page['title'];
    $view['revision'] = $rev_num;
    $view['page_name'] = $page['name'];
    $view['textblock'] = $page;

    // hack to select `profile` tab in top navigation bar
    if ($page['name'] == TB_USER_PREFIX.getattr($identity_user, 'username')) {
        $view['topnav_select'] = 'profile';
    }

    execute_view_die('views/textblock_view.php', $view);
}

// Show a textblock diff.
// FIXME: two revisions.
function controller_textblock_diff_revision($page_name) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-history', $page);
    } else {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }

    // Get revisions.
    // FIXME: probably doesn't work.
    $revfrom_id = (int)request("rev_from");
    $revto_id = (int)request("rev_to");
    if (is_null($revfrom_id) || is_null($revto_id)) {
        flash_error("Nu ati specificat reviziile");
        redirect(url($page_name));
    }

    $revfrom = textblock_get_revision($page_name, $revfrom_id);
    $revto = textblock_get_revision($page_name, $revto_id);
    if (is_null($revfrom) || is_null($revto)) {
        flash_error("Nu am gasit reviziile");
        redirect(url($page_name));
    }

    $diff_title = string_diff($revfrom['title'], $revto['title']);
    $diff_content = string_diff($revfrom['text'], $revto['text']);

    $view = array();
    $view['page_name'] = $page['name'];
    $view['title'] = "Diferente pentru $page_name intre reviziile $revfrom_id si $revto_id";
    $view['revfrom_id'] = $revfrom_id;
    $view['revto_id'] = $revto_id;
    $view['diff_title'] = explode("\n", $diff_title);
    $view['diff_content'] = explode("\n", $diff_content);
    execute_view_die('views/textblock_diff.php', $view);
}

// Restore a certain revision
// This copies the old revision on top.
function controller_textblock_restore_revision($page_name, $rev_num) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    $rev = textblock_get_revision($page_name, $rev_num);

    if ($page) {
        identity_require('textblock-restore', $page);
    } else {
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
                           getattr($identity_user, 'id'), $rev['security']);
    redirect(url($page_name));
}

// Display revisions
function controller_textblock_history($page_name) {
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-history', $page);
    } else {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }

    $options = pager_init_options();

    // revisions are browsed in reverse order, but get_revision_list
    // takes revision start/count parameters.
    // FIXME: This is ugly. Add reverse support in pager somehow?
    $total = textblock_get_revision_count($page_name);
    $start = $total - $options['first_entry'] - $options['display_entries'] + 1;
    if ($start < 1) {
        $count = $options['display_entries'] + $start - 1;
        $start = 1;
    } else {
        $count = $options['display_entries'];
    }
    log_print($options['first_entry']." -> ".$options['display_entries']. " $start -> $count, $total");
    $revs = textblock_get_revision_list(
            $page_name, false, true,
            $start, $count); 
    // FIXME: horrible hack, add revision_id column.
    for ($i = 0; $i < count($revs); ++$i) {
        $revs[$i]['revision_id'] = $start + $i; 
    }

    $view = array();
    $view['title'] = 'Istoria paginii '.$page_name;
    $view['page_name'] = $page['name'];
    $view['total_entries'] = $total;
    $view['revisions'] = array_reverse($revs);
    $view['first_entry'] = $options['first_entry'];
    $view['display_entries'] = $options['display_entries'];

    execute_view_die('views/textblock_history.php', $view);
}

// Edit a textblock
function controller_textblock_edit($page_name) {
    $page = textblock_get_revision($page_name);

    // permission check
    if ($page) {
        identity_require('textblock-edit', $page);
    } else {
        identity_require('textblock-create', $page);
    }

    $view = array();
    $form_errors = array();

    if (!$page) {
        $page_title = $page_name;
        $page_content = "Scrie aici despre " . $page_name;
        $page_security = "public";
        $view['title'] = "Creare " . $page_name;
    } else {
        $page_title = $page['title'];
        $page_content = $page['text'];
        $page_security = $page['security'];
        $view['title'] = "Editare " . $page_name;
    }

    // This is the creation action.
    $view['page_name'] = $page['name'];
    $view['action'] = url($page_name, array('action' => 'save'));
    if (identity_can('textblock-change-security')) {
        $view['form_values'] = array('content'=> $page_content,
                                     'title' => $page_title,
                                     'security' => $page_security);
    } else {
        $view['form_values'] = array('content'=> $page_content,
                                     'title' => $page_title);
    }
    $view['form_errors'] = $form_errors;
    execute_view_die("views/textblock_edit.php", $view);
}

// Save changes controller
function controller_textblock_save($page_name)
{
    // Permission check
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-edit', $page);
    } else {
        identity_require('textblock-create', $page);
    }

    // Get stuff from HTTP post
    $page_content = getattr($_POST, 'content', "");
    $page_title = getattr($_POST, 'title', "");
    $page_security = getattr($_POST, 'security', $page['security']);

    // Validate form data
    // FIXME: validation belongs in security code somehow.
    $form_errors = array();
    if ($page_security != $page['security']) {
        identity_require('textblock-change-security');
    }
    if (preg_match("/^ \s* task: \s* ([a-z0-9]*) \s* $/xi", $page_security, $matches)) {
        if (!task_get($matches[1])) {
            $form_errors['security'] = ("Nu exists task-ul <".$matches[1].">");
        }
    } else if (!preg_match("/^ \s* (private|public|protected) \s* $/xi", $page_security)) {
        $form_errors['security'] = "Descriptor de securitate gresit.";
    }
    if (strlen($page_content) < 1) {
        $form_errors['content'] = "Continutul paginii este prea scurt.";
    }
    if (strlen($page_title) < 1) {
        $form_errors['title'] = "Titlul este prea scurt.";
    }

    // It worked
    if (!$form_errors) {
        global $identity_user;
        textblock_add_revision($page_name, $page_title, $page_content, 
                               getattr($identity_user, 'id'),
                               $page_security);
        flash('Am actualizat continutul');
        redirect(url($page_name));
    }

    // It didn't work, back to editing.
    $view = array();
    $view['title'] = "Editare " . $page['name'];
    $view['page_name'] = $page['name'];
    $view['action'] = url($page_name, array('action' => 'save'));
    $form_values['content'] = $page_content;
    if (identity_can('textblock-change-security')) {
        $view['form_values'] = array('content'=> $page_content,
                                     'title' => $page_title,
                                     'security' => $page_security);
    } else {
        $view['form_values'] = array('content'=> $page_content,
                                     'title' => $page_title);
    }
    $view['form_errors'] = $form_errors;
    execute_view_die("views/textblock_edit.php", $view);
}

// Initial move controller.
function controller_textblock_move($page_name)
{
    // Get actual page.
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-move', $page);
    } else {
        // Missing page.
        flash_error("Pagina inexistenta.");
        redirect(url('home'));
    }

    $form_values = array();
    $form_values['new_name'] = "";

    // -- Print form
    $view = array(
            'title' => "Editare " . $page_name,
            'page_name' => $page_name,
            'action' => url($page_name, array('action' => 'move-submit')),
            'form_values' => $form_values,
            'form_errors' => array(),
    );
    execute_view_die("views/textblock_move.php", $view);
}

// Move submit controller.
function controller_textblock_move_submit($page_name)
{
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-move', $page);
    } else {
        // Missing page.
        flash_error("Pagina inexistenta");
    }

    // -- Get form values.
    $form_values = array();
    $form_values['new_name'] = $new_name = getattr($_POST, 'new_name', "");

    // -- Validate form values.
    $form_errors = array();
    if (textblock_get_revision($new_name)) {
        $form_errors['new_name'] = "Pagina deja exista";
    }

    if (!$form_errors) {
        // -- Do the monkey
        textblock_move($page_name, $new_name);
        flash("Pagina a fost mutata.");
        redirect(url($new_name));
    } else {
        // -- Back to form.
        $view = array(
                'title' => "Editare " . $page_name,
                'page_name' => $page_name,
                'action' => url($page_name, array('action' => 'move-submit')),
                'form_values' => $form_values,
                'form_errors' => $form_errors,
        );
        execute_view_die("views/textblock_move.php", $view);
    }
}

// Delete a certain textblock.
function controller_textblock_delete($page_name)
{
    // Get actual page.
    $page = textblock_get_revision($page_name);

    // If the page is missing jump to the edit/create controller.
    if ($page) {
        identity_require('textblock-delete', $page);
    } else {
        // Missing page.
        flash_error("Pagina inexistenta.");
    }
    textblock_delete($page_name);
    flash("Pagina a fost stearsa.");
    redirect(url('home'));
}

?>
