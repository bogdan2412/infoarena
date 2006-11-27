<?php

require_once(IA_ROOT."common/db/db.php");

// Get round
function round_get($round_id) {
    // this assert brakes templates pages with round_id = %round_id%
    // log_assert(is_round_id($round_id));
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
    $query = sprintf("SELECT `ia_round`.`id` AS `id`, `ia_round`.`type` AS `type`,
                             `ia_round`.`title` AS `title`,
                             `ia_round`.`hidden` AS `hidden`,
                             `ia_round`.`user_id` AS `user_id`
                      FROM `ia_round`
                      ORDER BY `ia_round`.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

// Create new round
function round_create($round) {
    assert(is_round($round));
    return db_insert('ia_round', $round);

/*    // FIXME: move in controller
      // FIXME: controller broken
    require_once(IA_ROOT . "common/textblock.php");
    $replace = array("round_id" => $round['id']);
    textblock_copy_replace("template/newround", $round['page_name'],
            $replace, "public", $round['user_id']);
*/
}

function round_update($round) {
    return db_update('ia_round', $round);
}

// Returns array with all tasks attached to the specified round
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
//
// FIXME: sensible ordering.
function round_get_task_info($round_id, $first = 0, $count = null) {
    if ($count === null) {
        $count = 490234;
    }
    $query = sprintf("SELECT
                        task_id AS id,
                        task.`title` AS `title`,
                        task.`page_name` AS `page_name`,
                        task.`hidden` AS `hidden`,
                        task.`user_id` AS `user_id`, 
                        task.`type` AS `type`
                      FROM ia_round_task
                      LEFT JOIN ia_task as task ON task.id = task_id
                      WHERE `round_id` = LCASE('%s')
                      ORDER BY task.`title`
                      LIMIT %d, %d",
                     db_escape($round_id), db_escape($first), db_escape($count));
    return db_fetch_all($query);
}

function round_get_task_count($round_id)
{
    $query = sprintf("SELECT COUNT(*) FROM ia_round_task
                    WHERE `round_id` = LCASE('%s')",
                    db_escape($round_id));
    return db_query_value($query);
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

/* FIXME: round registration disabled.
// Returns boolean whether given user is registered to round $round_id
function round_is_registered($round_id, $user_id) {
    $query = sprintf("SELECT COUNT(*) AS `cnt` FROM ia_user_round
                      WHERE round_id='%s' AND user_id='%s'",
                     db_escape($round_id), db_escape($user_id));

    $count = db_query_value($query);
    return (0 < $count);
}

// Registers user $user_id to round $round_id
// NOTE: This does not check for proper user permissions
//
// NOTE: There is a unique primary key constraint in the database for
// the pair (round_id, user_id). Registering the same user twice
// will fail.
function round_register_user($round_id, $user_id) {
    $insert_fields = array(
        "round_id" => $round_id,
        "user_id" => $user_id
    );
    return db_insert('ia_user_round', $insert_fields);
}
*/

?>
