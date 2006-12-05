<?php

require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "common/db/textblock.php");
require_once(IA_ROOT . "common/textblock.php");

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
                flash_error("Revizia \"{$rev_num}\" nu exista.");
                $page = $crpage;
            }
        } else {
            identity_require("textblock-view", $crpage);
            $page = $crpage;
        }
    } else {
        // Missing page.
        // FIXME: what if the user can't create the page?
        flash_error("Nu exista pagina, dar poti sa o creezi ...");
        redirect(url($page_name, array('action' => 'edit')));
    }

    log_assert_valid(textblock_validate($page));

    // Build view.
    $view = array();
    $view['title'] = $page['title'];
    $view['revision'] = $rev_num;
    $view['page_name'] = $page['name'];
    $view['textblock'] = $page;

    execute_view_die('views/textblock_view.php', $view);
}

// Show a textblock diff.
// FIXME: two revisions.
function controller_textblock_diff($page_name) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-history', $page);
    } else {
        flash_error("Aceasta pagina nu exista!");
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
    if ($revfrom_id == $revto_id) {
        flash_error("Reviziile sunt identice");
        redirect(url($page_name));
    }
    if ($revfrom_id < 1 || $revfrom_id < 1 || $revfrom_id > 100000 || $revfrom_id > 100000) {
        flash_error("Reviziile sunt invalide");
        redirect(url($page_name));
    }

    $revfrom = textblock_get_revision($page_name, $revfrom_id);
    $revto = textblock_get_revision($page_name, $revto_id);
    if (is_null($revfrom) || is_null($revto)) {
        flash_error("Nu am gasit reviziile");
        redirect(url($page_name));
    }
    log_assert_valid(textblock_validate($revfrom));
    log_assert_valid(textblock_validate($revto));

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
function controller_textblock_restore($page_name, $rev_num) {
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
    //log_print($options['first_entry']." -> ".$options['display_entries']. " $start -> $count, $total");
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

// Delete a certain textblock.
function controller_textblock_delete($page_name) {
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
