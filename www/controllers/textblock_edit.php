<?php

require_once(IA_ROOT_DIR . "common/db/textblock.php");
require_once(IA_ROOT_DIR . "common/tags.php");

// Edit a textblock
function controller_textblock_edit($page_name, $security = 'public') {
    // Need login for user id. No, really.
    identity_require_login();

    $page = textblock_get_revision($page_name);

    // permission check
    if ($page) {
        identity_require('textblock-edit', $page);
        $big_title = "Editare " . $page_name;
        $current_revision = textblock_get_revision_count($page_name);
    } else {
        $page = array(
                'name' => $page_name,
                'title' => $page_name,
                'text' => "Scrie aici despre " . $page_name,
                'security' => $security,
                'user_id' => identity_get_user_id(),
        );
        identity_require('textblock-create', $page);
        $big_title = "Creare " . $page_name;
        $current_revision = 0;
    }

    // Get form data
    $values = array();
    $values['text'] = request('text', $page['text']);
    $values['title'] = request('title', $page['title']);

    $security_values = array_map('trim', explode(':', $page['security']));
    $values['security'] = request('security', $security_values[0]);
    if (count($security_values) > 1) {
        $values['security_special'] = request('security_special', $security_values[1]);
    } else {
        $values['security_special'] = request('security_special', '');
    }

    $values['tags'] = request('tags', tag_build_list("textblock", $page_name, "tag"));
    $values['creation_timestamp'] = getattr($page, 'creation_timestamp');
    $values['timestamp'] = null;

    if (request_is_post()) {
        // Get new page
        $new_page['name'] = $page_name;
        $new_page['text'] = $values['text'];
        $new_page['title'] = $values['title'];

        if ($values['security_special'] != '') {
            $new_page['security'] = sprintf('%s: %s', $values['security'],
                                            $values['security_special']);
        } else {
            $new_page['security'] = $values['security'];
        }

        $new_page['creation_timestamp'] = $values['creation_timestamp'];
        $new_page['timestamp'] = $values['timestamp'];
        $new_page['user_id'] = identity_get_user_id();

        // Validate new page
        $errors = textblock_validate($new_page);

        // Check security.
        if ($new_page['security'] != $page['security']) {
            identity_require('textblock-change-security', $page);
        }

        // Handle tags
        tag_validate($values, $errors);

        // Check if page was edited by another user in the meantime
        if (request('last_revision') != $current_revision) {
            $errors['was_modified'] = 'Pagina a fost editată între timp de către alt utilizator. ' .
                                      'Revizia pe care o editați avea numărul <b>' . request('last_revision') . '</b>, în timp ce revizia curentă are numărul <b>' . $current_revision . '</b>. ' .
                                      'Puteti vedea diferențele <a target="_blank" href="' . url_textblock_diff($page_name, (int)request('last_revision'), $current_revision) . '">aici</a>.';
        }

        // It worked
        if (!$errors) {
            textblock_add_revision($new_page['name'], $new_page['title'],
                                   $new_page['text'], $new_page['user_id'],
                                   $new_page['security'],
                                   $new_page['timestamp'],
                                   $new_page['creation_timestamp'],
                                   remote_ip_info());
            if (identity_can('textblock-tag', $new_page)) {
                tag_update("textblock", $new_page['name'], "tag", $values['tags']);
            }
            flash('Am actualizat conținutul.');

            // Redirect depends of the pressed submit button
            $save_and_edit = getattr($_POST, 'form_save_and_edit');
            if($save_and_edit == null)
                redirect(url_textblock($page_name));
            else
                redirect(url_textblock_edit($page_name));
        }
    } else {
        $errors = array();
    }

    if (!identity_can('textblock-change-security', $page)) {
        unset($values['security']);
    }

    // Create view.
    $view = array();
    $view['title'] = $big_title;
    $view['page'] = $page;
    $view['page_name'] = $page_name;
    $view['last_revision'] = $current_revision;
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;

    execute_view_die("views/textblock_edit.php", $view);
}

?>
