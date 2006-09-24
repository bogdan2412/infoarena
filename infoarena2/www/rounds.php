<?php
/**
 * Contains contest round logic
 * Note: We do not put database calls in here. Just logic please.
 *
 * FIXME: There seems to be a .htaccess/mod_rewrite bug. For some reason,
 * naming this file `round.php` fucks up URLs to contest rounds such as:
 * `round/preoni2007/1/9-10`
 */

// Returns a list of tasks filtered by effective user permissions
// for a given action.
//
// $action is view/submit/edit
// Optionally pass $all_tasks array with a list of tasks you wish to filter.
function round_get_permitted_tasks($round_id, $action, $all_tasks = null) {
    log_assert('view' == $action || 'submit' == $action || 'edit' == $action,
              'Invalid contest round action.');

    if (is_null($all_tasks)) {
        $all_tasks = round_get_task_info($round_id);
    }

    $tasks = array();
    foreach ($all_tasks as $k => $task) {
        if (identity_can('task-' . $action, $task)) {
            $tasks[$k] = $task;
        }
    }

    return $tasks;
}

?>
