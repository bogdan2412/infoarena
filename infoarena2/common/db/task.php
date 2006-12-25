<?php

require_once(IA_ROOT . "common/db/db.php");
require_once(IA_ROOT . "common/task.php");
require_once(IA_ROOT . "common/db/parameter.php");

// Task-related db functions.

// Get task by id. No params.
function task_get($task_id) {
    log_assert(is_task_id($task_id));
    $query = sprintf("SELECT * FROM ia_task WHERE `id` = LCASE('%s')",
                     db_escape($task_id));
    $res = db_fetch($query);
    if ($res) {
        log_assert_valid(task_validate($res));
    }
    return $res;
}

// create new task
function task_create($task, $task_params) {
    log_assert_valid(task_validate($task));
    log_assert_valid(task_validate_parameters($task['type'], $task_params));

    $res = db_insert('ia_task', $task);
    if ($res) {
        // Insert parameters.
        task_update_parameters($task['id'], $task_params);

        // Copy templates.
        require_once(IA_ROOT . "common/textblock.php");
        $replace = array("task_id" => $task['id']);
        textblock_copy_replace("template/newtask", $task['page_name'],
                $replace, "task: {$task['id']}", $task['user_id']);
    }
    return $res;
}

function task_update($task) {
    log_assert_valid(task_validate($task));
    return db_update('ia_task', $task,
            "`id` = '".db_escape($task['id'])."'");
}

// binding for parameter_get_values
function task_get_parameters($task_id) {
    log_assert(is_task_id($task_id));
    return parameter_get_values('task', $task_id);
}

// binding for parameter_update_values
function task_update_parameters($task_id, $param_values) {
    log_assert(is_task_id($task_id));
    return parameter_update_values('task', $task_id, $param_values);
}

// Get all tasks.
// FIXME: paging?
function task_get_all() {
    return db_fetch_all("SELECT * FROM ia_task");
}

// Get all tasks as an array mapping task_id to task.
// FIXME: paging?
function task_get_all_assoc() {
    $list = array();
    foreach (task_get_all() as $task) {
        $list[$task['id']] = $task;
    }
    return $list;
}

// Returns list of round ids that include this task
function task_get_parent_rounds($task_id) {
    log_assert(is_task_id($task_id));
    $query = sprintf("
        SELECT DISTINCT round_id
        FROM ia_round_task
        WHERE task_id='%s'
        ORDER BY round_id
    ", db_escape($task_id));

    $rows = db_fetch_all($query);

    // transform rows into id list
    $idlist = array();
    foreach ($rows as $row) {
        $idlist[] = $row['round_id'];
    }

    return $idlist;
}

?>
