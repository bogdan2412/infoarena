<?php

require_once("db.php");

// Task-related db functions.

// Get task by id. No params.
function task_get($task_id) {
    $query = sprintf("SELECT * FROM ia_task WHERE `id` = LCASE('%s')",
                     db_escape($task_id));
    $res = db_fetch($query);
    if ($res) {
        log_assert_valid(task_validate($res));
    }
    return $res;
}

// create new task
function task_create($task) {
    log_assert_valid(task_validate($task));
    return db_insert('ia_task', $task);
}

function task_update($task) {
    log_assert_valid(task_validate($task));
    return db_update('ia_task', $task);
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

// Returns array with all tasks available
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function task_list_info() {
    $query = sprintf("SELECT *
                      FROM ia_task
                      ORDER BY ia_task.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

// Returns list of round ids that include this task
function task_get_parent_rounds($task_id) {
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
