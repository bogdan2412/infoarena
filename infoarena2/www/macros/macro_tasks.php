<?php

require_once(IA_ROOT . "www/format/table.php");

// Lists all tasks attached to a given round
// Takes into consideration user permissions.
//
// Arguments;
//      round_id (required)     Round identifier
//
// Examples:
//      Tasks(round_id="archive")
//
// FIXME: print current user score, difficulty rating, etc.
// FIXME: security. Only reveals task names, but still...
function macro_tasks($args) {
    $options = pager_init_options($args);

    $round_id = getattr($args, 'round_id');
    if (!$round_id) {
        return macro_error('Expecting argument `round_id`');
    }

    // fetch round info
    $round = round_get($round_id);
    if (!$round) {
        return macro_error('Invalid round identifier');
    }
    /*
    if (!identity_can('round-viewtasks', $round)) {
        return "<b>Nu ai voie sa vezi problemele.</b>"
        return macro_permission_error();
    }
    */

    // get round tasks
    $tasks = round_get_task_info($round_id, $options['first_entry'], $options['display_entries']);
    $options['total_entries'] = round_get_task_count($round_id);

    // For the task column.
    function format_task_link($row)
    {
        return '<a href="' . $row['page_name']. '">' . $row['title'] . '</a>';
    }

    $column_infos = array(
            array(
                'title' => 'Titlul problemei',
                'rowform' => 'format_task_link',
            ),
    );

    return format_table($tasks, $column_infos, $options);
}

?>
