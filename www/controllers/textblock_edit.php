<?php

require_once(IA_ROOT_DIR . "common/db/textblock.php");
require_once(IA_ROOT_DIR . "common/tags.php");

// Edit a textblock
function controller_textblock_edit($page_name) {
    // Need login for user id. No, really.
    identity_require_login();

    $page = textblock_get_revision($page_name);

    // permission check
    if ($page) {
        identity_require('textblock-edit', $page);
        $big_title = "Editare " . $page_name;
    } else {
        $page = array(
                'name' => $page_name,
                'title' => $page_name,
                'text' => "Scrie aici despre " . $page_name,
                'security' => 'public',
                'user_id' => identity_get_user_id(),
        );
        identity_require('textblock-create', $page);
        $big_title = "Creare " . $page_name;
    }

    // Get form data
    $values = array();
    $values['text'] = request('text', $page['text']);
    $values['title'] = request('title', $page['title']);
    $values['security'] = request('security', $page['security']);
    $values['tags'] = request('tags', tag_build_list("textblock", $page_name));
    $values['creation_timestamp'] = getattr($page, 'creation_timestamp');

    if (request_is_post()) {
        // Get new page
        $new_page['name'] = $page_name;
        $new_page['text'] = $values['text'];
        $new_page['title'] = $values['title'];
        $new_page['security'] = $values['security'];
        $new_page['creation_timestamp'] = $values['creation_timestamp'];
        $new_page['user_id'] = identity_get_user_id();

        // Validate new page
        $errors = textblock_validate($new_page);

        // Check security.
        if ($new_page['security'] != $page['security']) {
            identity_require('textblock-change-security', $page);
        }

        // Handle tags
        tag_validate($values, $errors);

        // It worked
        if (!$errors) {
            textblock_add_revision($new_page['name'], $new_page['title'],
                                   $new_page['text'], $new_page['user_id'],
                                   $new_page['security'], $new_page['creation_timestamp']);
            if (identity_can('textblock-tag', $new_page)) {
                tag_update("textblock", $new_page['name'], $values['tags']);
            }
            flash('Am actualizat continutul');
            redirect(url_textblock($page_name));
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
    $view['form_values'] = $values;
    $view['form_errors'] = $errors;

    execute_view_die("views/textblock_edit.php", $view);
}

?>
