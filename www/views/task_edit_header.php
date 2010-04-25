<?php

require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "www/format/list.php");
require_once(IA_ROOT_DIR . "www/url.php");

function task_edit_tabs($task_id, $active) {
    $tabs = array();

    $tab_names = array('edit' => 'Enunţ',
                       'task-edit-params' => 'Parametri',
                       'task-edit-tags' => 'Taguri',
                       'task-edit-ratings' => 'Ratinguri');

    $permissions = array('edit' => 'task-edit',
                         'task-edit-params' => 'task-edit',
                         'task-edit-tags' => 'task-tag',
                         'task-edit-ratings' => 'task-edit-ratings');

    $task = task_get($task_id);
    foreach ($tab_names as $action => $text) {
        if (identity_can($permissions[$action], $task)) {
            $tabs[$action] = format_link(
                                url_task_edit($task_id, $action), $text);
        }
    }
    $tabs[$active] = array($tabs[$active], array('class' => 'active'));

    return format_ul($tabs, 'htabs');
}

?>
