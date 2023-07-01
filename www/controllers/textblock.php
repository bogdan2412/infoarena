<?php

require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "common/db/textblock.php");
require_once(IA_ROOT_DIR . "common/textblock.php");
require_once(IA_ROOT_DIR . "common/diff.php");


// View a plain textblock.
// That textblock can be owned by something else.
function controller_textblock_view($page_name, $rev_num = null,
        $display_view = 'views/textblock_view.php') {
    global $identity_user;

    // Get actual page.
    $crpage = textblock_get_revision($page_name);

    // If the page is missing jump to the edit/create controller.
    if ($crpage) {
        // Checks if $rev_num is the latest.
        $rev_count = textblock_get_revision_count($page_name);
        if ($rev_num && $rev_num != $rev_count) {
            if (!is_numeric($rev_num) || (int)$rev_num < 1) {
                flash_error('Revizia "' . $rev_num . '" este invalidă.');
                redirect(url_textblock($page_name));
            } else {
                $rev_num = (int)$rev_num;
            }
            identity_require("textblock-history", $crpage);
            $page = textblock_get_revision($page_name, $rev_num);

            if (!$page) {
                flash_error('Revizia "' . $rev_num . '" nu există.');
                redirect(url_textblock($page_name));
            }
        } else {
            identity_require("textblock-view", $crpage);
            $page = $crpage;
        }
    } else {
        // Missing page.
        flash_error("Nu există pagina, dar poți să o creezi.");
        redirect(url_textblock_edit($page_name));
    }

    log_assert_valid(textblock_validate($page));

    // Build view.
    $view = array();
    $view['title'] = $page['title'];
    $view['revision'] = $rev_num;
    $view['revision_count'] = $rev_count;
    $view['page_name'] = $page['name'];
    $view['textblock'] = $page;
    if ($page_name == 'admin') {
        $view['topnav_select'] = 'admin';
    }
    execute_view_die($display_view, $view);
}

// Show differences between two textblock revisions.
function controller_textblock_diff($page_name) {
    global $identity_user;
    $page = textblock_get_revision($page_name);
    $rev_count = textblock_get_revision_count($page_name);
    if ($page) {
        identity_require('textblock-history', $page);
    } else {
        flash_error("Această pagină nu există.");
        redirect(url_home());
    }

    // Validate revision ids.
    $revfrom_id = request("rev_from");
    $revto_id = request("rev_to");
    if (is_null($revfrom_id) || is_null($revto_id)) {
        flash_error("Nu ați specificat reviziile.");
        redirect(url_textblock($page_name));
    }
    if (!is_whole_number($revfrom_id) || !is_whole_number($revto_id) ||
        $revfrom_id < 1 || $revfrom_id > $rev_count ||
        $revto_id < 1 || $revto_id > $rev_count) {
        flash_error("Reviziile sunt invalide.");
        redirect(url_textblock($page_name));
    }
    if ($revfrom_id == $revto_id) {
        flash_error("Reviziile sunt identice.");
        redirect(url_textblock($page_name));
    }

    // Get revisions.
    $revfrom = textblock_get_revision($page_name, $revfrom_id);
    $revto = textblock_get_revision($page_name, $revto_id);
    if (is_null($revfrom) || is_null($revto)) {
        log_error("Unable to get revisions $revfrom and $revto for " .
                  "textblock $page_name.");
    }
    log_assert_valid(textblock_validate($revfrom));
    log_assert_valid(textblock_validate($revto));

    // Get diffs.
    $diff_title = diff_inline(array($revfrom['title'], $revto['title']));
    $diff_content = diff_inline(array($revfrom['text'], $revto['text']));
    $diff_security = diff_inline(array($revfrom['security'],
                                       $revto['security']));

    $view = array();
    $view['page_name'] = $page['name'];
    $view['title'] = 'Diferențe pentru ' . $page_name . ' între reviziile ' .
                     $revfrom_id . ' și ' . $revto_id;
    $view['rev_count'] = $rev_count;
    $view['revfrom_id'] = $revfrom_id;
    $view['revto_id'] = $revto_id;
    $view['diff_title'] = $diff_title;
    $view['diff_content'] = $diff_content;
    $view['diff_security'] = $diff_security;
    execute_view_die('views/textblock_diff.php', $view);
}

// Restore a certain revision
// This copies the old revision on top.
function controller_textblock_restore($page_name, $rev_num) {
    if (!request_is_post()) {
        flash_error("Pagina nu a putut fi înlocuită.");
        redirect(url_textblock($page_name));
    }

    global $identity_user;
    $page = textblock_get_revision($page_name);
    $rev = textblock_get_revision($page_name, $rev_num);

    if ($page and $rev) {
        identity_require('textblock-restore', $page);
        identity_require('textblock-restore', $rev);
    } else {
        flash_error("Pagina nu există.");
        redirect(url_home());
    }

    if (is_null($rev_num)) {
        flash_error("Nu ați specificat revizia.");
        redirect(url_textblock($page_name));
    }
    if (!$rev) {
        flash_error("Revizia nu există.");
        redirect(url_textblock($page_name));
    }

    textblock_add_revision($rev['name'], $rev['title'], $rev['text'],
                           getattr($identity_user, 'id'), $rev['security'],
                           null,
                           $rev['creation_timestamp'],
                           remote_ip_info());
    flash("Pagina a fost înlocuită cu revizia {$rev_num}.");
    redirect(url_textblock($page_name));
}

// Display revisions
function controller_textblock_history($page_name) {
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('textblock-history', $page);
    } else {
        flash_error("Pagina nu există.");
        redirect(url_home());
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
    if (!request_is_post()) {
        flash_error("Pagina nu a putut fi ștearsă.");
        redirect(url_textblock($page_name));
    }

    // Get actual page.
    $page = textblock_get_revision($page_name);

    // If the page is missing jump to the edit/create controller.
    if ($page) {
        identity_require('textblock-delete', $page);
    } else {
        // Missing page.
        flash_error("Pagină inexistentă.");
        redirect(url_home);
    }
    textblock_delete($page_name);
    flash("Pagina a fost ștearsă.");
    redirect(url_home());
}

// Delete a list of textblocks
function controller_textblock_delete_many($textblocks, $redirect) {
    $deleted = 0;
    $not_deleted_because_of_permision = 0;
    $bad_page_names = 0;

    if (!is_array($textblocks)) {
        flash_error("Nu ați specificat pagini pentru a fi șterse.");
        redirect($redirect);
    }

    foreach ($textblocks as $name) {
        if (!is_page_name($name)) {
            ++$bad_page_names;
        } else if (identity_can("textblock-delete",
                                textblock_get_revision($name))) {
            $deleted += textblock_delete($name);
        } else {
            ++$not_deleted_because_of_permision;
        }
    }

    flash($deleted." textblockuri au fost șterse.");
    if ($not_deleted_because_of_permision) {
        flash($not_deleted_because_of_permision.
              " textblockuri nu au putut fi șterse din cauza permisiunilor.");
    }
    if ($bad_page_names) {
        flash($bad_page_names." textblockuri au numele corupt.");
    }
    redirect($redirect);
}

// Delete a certain revision
function controller_textblock_delete_revision($page = null, $rev_num = null) {
    if (!request_is_post()) {
        flash_error("Pagina nu a putut fi ștearsă.");
        redirect(url_textblock($page));
    }

    if ($page == null) {
        flash_error("Nu a fost specificată pagina.");
        redirect(url_home());
    }
    if ($rev_num == null) {
        flash_error("Nu a fost specificat numărul reviziei.");
        redirect(url_home());
    }

    $total_revs = textblock_get_revision_count($page);
    if ($rev_num > $total_revs) {
        flash_error("Nu există revizia.");
        redirect(url_home());
    }

    $revision = textblock_get_revision(
        $page, $rev_num == $total_revs ? null : $rev_num);
    if ($total_revs == 1) {
        identity_require('textblock-delete', $revision);
        textblock_delete($page);
    }

    if ($revision) {
        identity_require('textblock-delete-revision', $revision);
    } else {
        flash_error("Revizie inexistentă.");
        redirect(url_home());
    }

    textblock_delete_revision($revision, $rev_num == $total_revs);

    flash("Revizia a fost ștearsă.");
    redirect(url_textblock_history($page));
}
