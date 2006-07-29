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

            // generate mark-up
            $output = wiki_process_text($page_content, $page_name);
            $view['json'] = array('html' => $output);
            $view['debug'] = request('debug', null);

            // output JSON
            include('views/json.php');
            break;

        default:
            flash('Actiunea nu este valida.');
            redirect(url(''));
    }
}


?>
