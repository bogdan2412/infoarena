<?php

require_once("db.php");

/**
 * Round stuff. Mostly evil.
 */
function round_get($round_id) {
    $query = sprintf("SELECT * FROM ia_round WHERE `id` = LCASE('%s')",
                     db_escape($round_id));
    return db_fetch($query);
}

// Returns array with all open rounds
//
// :WARNING: This does not select all fields related to each round,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function round_get_info() {
    global $dbLink;
    $query = sprintf("SELECT ia_round.id AS id, ia_round.`type` AS `type`,
                             tblock.title AS title,
                             ia_round.`active` AS `active`,
                             ia_round.user_id AS user_id
                      FROM ia_round
                      LEFT JOIN ia_textblock AS tblock
                        ON tblock.`name` = CONCAT('round/', ia_round.id)
                      ORDER BY tblock.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

function round_get_textblock($round_id) {
    return textblock_get_revision('round/' . $round_id);
}

function round_create($round_id, $type, $user_id, $active) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_round
                        (`id`, `type`, user_id, `active`)
                      VALUES (LCASE('%s'), '%s', '%s', '%s')",
                     db_escape($round_id), db_escape($type),
                     db_escape($user_id), db_escape($active));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function round_update($round_id, $type, $active) {
    global $dbLink;
    $query = sprintf("UPDATE ia_round
                      SET `type` = '%s', `active` = '%s'
                      WHERE `id` = LCASE('%s')
                      LIMIT 1",
                     db_escape($type), db_escape($active),
                     db_escape($round_id));
    return db_query($query);
}

// Returns array with all tasks attached to the specified round
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function round_get_task_info($round_id) {
    global $dbLink;
    $query = sprintf("SELECT
                        task_id AS id, tblock.title AS title,
                        ia_task.`hidden` AS `hidden`,
                        ia_task.user_id AS user_id, ia_task.`type` AS `type`
                      FROM ia_round_task
                      LEFT JOIN ia_task ON ia_task.id = task_id
                      LEFT JOIN ia_textblock AS tblock
                        ON tblock.`name` = CONCAT('task/', task_id)
                      WHERE `round_id` = LCASE('%s')
                      ORDER BY tblock.`title`",
                     db_escape($round_id));
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

// binding for parameter_get_values
function round_get_parameters($round_id) {
    return parameter_get_values('round', $round_id);
}

// binding for parameter_update_values
function round_update_parameters($round_id, $param_values) {
    return parameter_update_values('round', $round_id, $param_values);
}

// Replaces attached task list for given round
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $tasks is array of task id's
function round_update_task_list($round_id, $tasks) {
    // delete all round-task relations
    $query = sprintf("DELETE FROM ia_round_task
                      WHERE round_id = LCASE('%s')",
                     db_escape($round_id));
    db_query($query);

    // insert new relations
    foreach ($tasks as $task_id) {
        $query = sprintf("INSERT INTO ia_round_task
                            (round_id, task_id)
                          VALUES ('%s', '%s')",
                         db_escape($round_id), db_escape($task_id));
        db_query($query);
    }
}

?>
