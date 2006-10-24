<?php

// This controller serves as a data server for AJAX requests.
// Instead of generating HTML content to be displayed in a browser,
// data computed by JSON controllers is served in JSON format.
//
// FIXME: separate functions and url magic?
// it works this way.
function controller_json($suburl) {
    switch ($suburl) {
        case 'wiki-preview':
            // Parse wiki markup and return JSON with HTML output.
            // This is used for previewing markup when editing the wiki.
            $page_content = request('content');
            $page_name = request('page_name');

            // get text block
            $textblock = textblock_get_revision($page_name);
            log_assert($textblock, 'Invalid textblock identifier');

            // generate mark-up
            $output = wiki_process_text($page_content);
            $json = array('html' => $output);

            // view
            $view = array(
                'json' => $json,
                'debug' => request('debug', null)
            );

            // output JSON
            execute_view_die('views/json.php', $view);

        default:
            flash('Actiunea nu este valida.');
            redirect(url(''));
    }
}


?>
