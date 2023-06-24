<?php

require_once(IA_ROOT_DIR . 'common/db/job.php');
require_once(IA_ROOT_DIR . 'common/db/task.php');
require_once(IA_ROOT_DIR . 'common/db/textblock.php');
require_once(IA_ROOT_DIR . 'lib/Wiki.php');

// This controller serves as a data server for AJAX requests.
// Instead of generating HTML content to be displayed in a browser,
// data computed by JSON controllers is served in JSON format.
//
// FIXME: separate functions and url magic?
// it works this way.
function controller_json($suburl) {
    // FIXME: We need to refactor this,
    // don't put all json requests in controllers/json.php
    switch ($suburl) {
        case 'wiki-preview':
            // Parse wiki markup and return JSON with HTML output.
            // This is used for previewing markup when editing the wiki.
            $page_name = request('page_name');
            $page_content = file_get_contents('php://input');

            // get text block
            $textblock = textblock_get_revision($page_name);
            log_assert($textblock, 'Invalid textblock identifier: '.$page_name);

            // check permissions & generate mark-up
            if (!identity_can('textblock-view', $textblock)) {
                $output = 'Not enough privileges to preview this page';
            } else {
                $output = Wiki::processText($page_content);
            }

            // view
            $json = array('html' => $output);
            $view = array(
                'json' => $json,
                'debug' => request('debug', null)
            );

            // output JSON
            execute_view_die('views/json.php', $view);

        case 'task-get-rounds':
            // Return list of parent rounds for a task
            $task_id = request('task_id');
            if (!is_task_id($task_id)) {
                // Die with error code 400 Bad Request
                die_http_error(400, 'Task invalid.');
            }

            require_once(IA_ROOT_DIR . "www/views/submit_form.php");
            $json = task_get_submit_options($task_id);
            $view = array(
                'json' => $json,
                'debug' => request('debug', null)
            );

            // Output JSON
            execute_view_die('views/json.php', $view);

        case 'job-skip':
            $job_id = request('job_id');
            if (!is_job_id($job_id)) {
                die_http_error(400, 'Job invalid.');
            }

            $job = job_get_by_id($job_id);
            if ($job === null) {
                die_http_error(400, 'Job inexistent.');
            }

            if (!identity_can('job-skip', $job)) {
                die_http_error(403, 'Nu ai destule permisiuni.');
            }

            job_update($job['id'], 'skipped');
            $view = array(
                'json' => true,
                'debug' => request('debug', null));
            execute_view_die('views/json.php', $view);
            break;

        default:
            die_http_error(400, 'Acțiune invalidă.');
    }
}
