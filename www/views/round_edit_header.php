<?php

require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "www/format/list.php");
require_once(IA_ROOT_DIR . "www/url.php");

function round_edit_tabs($round_id, $active) {
    $tabs = array();

    $tab_names = array(
        'edit' => 'Pagină',
        'round-edit-params' => 'Parametri',
        'round-edit-task-order' => 'Ordine probleme');

    $tab_urls = array(
        'edit' => url_round_edit($round_id),
        'round-edit-params' => url_round_edit_params($round_id),
        'round-edit-task-order' => url_round_edit_task_order($round_id));

    $permissions = array(
        'edit' => 'round-edit',
        'round-edit-params' => 'round-edit',
        'round-edit-task-order' => 'round-edit');

    $round = round_get($round_id);

    foreach ($tab_names as $action => $text) {
        if (identity_can($permissions[$action], $round)) {
            $tabs[$action] = format_link($tab_urls[$action], $text);
        }
    }
    $tabs[$active] = array($tabs[$active], array('class' => 'active'));

    return format_ul($tabs, 'htabs');
}

?>
