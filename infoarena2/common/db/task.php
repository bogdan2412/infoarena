<?php

require_once("db.php");

// Task-related db functions.

// Get task by id. No params.
function task_get($task_id) {
    $query = sprintf("SELECT * FROM ia_task WHERE `id` = LCASE('%s')",
                     db_escape($task_id));
    return db_fetch($query);
}

// Get the textblock associated to a task.
function task_get_textblock($task_id) {
    return textblock_get_revision('task/' . $task_id);
}

//
function task_create($task_id, $type, $hidden, $author, $source, $user_id) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_task
                        (`id`, `type`, `hidden`, author, `source`, user_id)
                      VALUES (LCASE('%s'), '%s', '%s', '%s', '%s', '%s')",
                      db_escape($task_id), db_escape($type),
                      db_escape($hidden), db_escape($author),
                      db_escape($source), db_escape($user_id));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function task_update($task_id, $type, $hidden, $author, $source) {
    global $dbLink;
    $query = sprintf("UPDATE ia_task
                      SET author = '%s', `source` = '%s', `type` = '%s',
                          `hidden` = '%s'
                      WHERE `id` = LCASE('%s')
                      LIMIT 1",
                     db_escape($author), db_escape($source),
                     db_escape($type), db_escape($hidden),
                     db_escape($task_id));
    return db_query($query);
}

// binding for parameter_get_values
function task_get_parameters($task_id) {
    return parameter_get_values('task', $task_id);
}

// binding for parameter_update_values
function task_update_parameters($task_id, $param_values) {
    return parameter_update_values('task', $task_id, $param_values);
}

// Returns array with all tasks available
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function task_list_info() {
    global $dbLink;
    $query = sprintf("SELECT ia_task.id AS id, tblock.title AS title,
                        ia_task.`hidden` AS `hidden`, ia_task.`type` AS `type`
                      FROM ia_task
                      LEFT JOIN ia_textblock AS tblock
                        ON tblock.`name` = CONCAT('task/', ia_task.id)
                      ORDER BY tblock.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

?>
