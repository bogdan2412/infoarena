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

// create new task
function task_create($task_id, $type, $hidden, $author, $source, $user_id) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_task
                        (`id`, `type`, `hidden`, author, `source`, user_id)
                      VALUES (LCASE('%s'), '%s', '%s', '%s', '%s', '%s')",
                      db_escape($task_id), db_escape($type),
                      db_escape($hidden), db_escape($author),
                      db_escape($source), db_escape($user_id));

    // create database entry for new task
    log_print('Creating database entry for task: '.$task_id);
    db_query($query);
    $new_task = task_get($task_id);
    log_assert($new_task, 'New task input was validated OK but no database entry was created');

    // create associated textblock entry
    // default (initial) content is taken from an existing template
    $template = textblock_get_revision('template/newtask');
    log_assert($template, 'Could not find template for new task: template/newtask');
    $title = str_replace('%task_id%', $new_task['id'], $template['title']);
    $content = str_replace('%task_id%', $new_task['id'], $template['text']);
    textblock_add_revision('task/'.$new_task['id'], $title, $content, $user_id);

    return $new_task['id'];
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
