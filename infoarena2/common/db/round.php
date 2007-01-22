<?php

require_once(IA_ROOT."common/db/db.php");
require_once(IA_ROOT."common/db/parameter.php");

// Get round
function round_get($round_id) {
    // this assert brakes templates pages with round_id = %round_id%
    log_assert(is_round_id($round_id));
    $query = sprintf("SELECT * FROM ia_round WHERE `id` = LCASE('%s')",
                     db_escape($round_id));
    return db_fetch($query);
}

// Returns array with all open rounds
// FIXME: obliterate
function round_get_info() {
    $query = sprintf("SELECT *
                      FROM `ia_round`
                      ORDER BY `ia_round`.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

// Create new round
// Return success.
function round_create($round, $round_params, $user_id) {
    log_assert(is_user_id($user_id));
    log_assert_valid(round_validate($round));
    log_assert_valid(round_validate_parameters($round['type'], $round_params));

    db_insert('ia_round', $round);
    $new_round = round_get($round['id']);

    if ($new_round) {
        round_update_parameters($round['id'], $round_params);

        // Copy templates.
        require_once(IA_ROOT . "common/textblock.php");
        $replace = array("round_id" => $round['id']);
        textblock_copy_replace("template/newround", $round['page_name'],
                $replace, "round: {$round['id']}", $user_id);

        return true;
    } else {
        return false;
    }
}

// Update a round.
// FIXME: crap code.
function round_update($round) {
    log_assert_valid(round_validate($round));
    return db_update('ia_round', $round,
            "`id` = '".db_escape($round['id'])."'");
}

// Returns array with all tasks attached to the specified round
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
//
// FIXME: sensible ordering.
//
// if user_id is non-null a join is done on $score
function round_get_tasks($round_id, $first = 0, $count = null, $user_id = null, $score_name = null) {
    if ($count === null) {
        $count = 666013;
    }
    $fields = "round_task.task_id AS id, ".
              "task.`order` AS `order`, ".
              "task.`title` AS `title`, ".
              "task.`page_name` AS `page_name`, ".
              "task.`hidden` AS `hidden`, ".
              "task.`type` AS `type` ";

    if ($score_name === null || $user_id === null) {
        $query = sprintf("SELECT $fields
                          FROM ia_round_task as round_task
                          LEFT JOIN ia_task as task ON task.id = round_task.task_id
                          WHERE `round_task`.`round_id` = LCASE('%s')
                          ORDER BY task.`order` LIMIT %d, %d",
                         db_escape($round_id), db_escape($first), db_escape($count));
    } else {
        log_assert(is_whole_number($user_id));
        $query = sprintf("SELECT $fields, score.score as score
                          FROM ia_round_task as round_task
                          LEFT JOIN ia_task as task ON task.id = round_task.task_id
                          LEFT JOIN ia_score as score ON
                                score.user_id = %s AND
                                score.name = '%s' AND
                                score.round_id = LCASE('%s') AND
                                score.task_id = round_task.task_id
                          WHERE `round_task`.`round_id` = LCASE('%s')
                          ORDER BY task.`order` LIMIT %d, %d",
                         db_escape($user_id), db_escape($score_name), db_escape($round_id),
                         db_escape($round_id), db_escape($first), db_escape($count));
    }
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
    log_assert(is_round_id($round_id));

    // delete all round-task relations
    $query = sprintf("DELETE FROM ia_round_task
                      WHERE round_id = '%s'",
                     db_escape($round_id));
    db_query($query);

    // insert new relations
    foreach ($tasks as $task_id) {
        $values[] = "('".db_escape($round_id)."', '".db_escape($task_id)."')";
    }
    $query = "INSERT INTO ia_round_task (round_id, task_id) 
              VALUES ". implode(', ', $values);
    db_query($query);
}

// Return list of round ids
function round_get_list() {
    $query = "SELECT id FROM ia_round";
    $rows = db_fetch_all($query);

    $list = array();
    foreach ($rows as $row) {
        $list[] = $row['id'];
    }

    return $list;
}

// Returns boolean whether given user is registered to round $round_id
function round_is_registered($round_id, $user_id) {
    log_assert(is_round_id($round_id));
    log_assert(is_user_id($user_id));

    //FIXME: this should not be here
    if ('arhiva' == $round_id) {
        return true;
    }

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
    log_assert(is_round_id($round_id));
    $insert_fields = array(
        "round_id" => $round_id,
        "user_id" => $user_id
    );
    return db_insert('ia_user_round', $insert_fields);
}

// Returs list of registred user to round $round_id, order by rating
function round_get_registered_users_range($round_id, $start, $range)
{
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert(is_round_id($round_id));
    log_assert($start >= 0);
    log_assert($range >= 0);
    db_query("SET @counter = ".$start);

    //FIXME: This should not be here
    $round = round_get($round_id);
    if ($round['type'] == 'archive') {
        $query = sprintf("SELECT id AS user_id, rating_cache AS rating,
                          username, full_name AS fullname,
                          (@counter := @counter + 1) AS position
                          FROM ia_user 
                          ORDER BY rating DESC
                          LIMIT %s, %s", $start, $range);
    }
    else {
        $query = sprintf("SELECT user_id, user.rating_cache AS rating,
                          user.username AS username, user.full_name AS fullname,
                          (@counter := @counter + 1) AS position
                          FROM ia_user_round 
                          LEFT JOIN ia_user AS user ON user_id = user.id
                          WHERE round_id = LCASE('%s')
                          ORDER BY rating DESC
                          LIMIT %s, %s", db_escape($round_id), $start, $range);
    }
    return db_fetch_all($query);
}

// Returns number of registered users in a certain round
function round_get_registered_users_count($round_id)
{
    log_assert(is_round_id($round_id));

    //FIXME: This should not be here.. I bet this is horribly wrong somehow :)
    $round = round_get($round_id);
    if ($round['type'] == 'archive') {
        $query = "SELECT COUNT(*) FROM ia_user";
    }
    else
    {
        $query = sprintf("SELECT COUNT(*) FROM ia_user_round
                          WHERE `round_id` = LCASE('%s')",
                          db_escape($round_id));
    }
    return db_query_value($query);
}

// Makes all tasks visible
// No error handling.
// FIXME: task.hidden is stupid, we need proper security
function round_unhide_all_tasks($round_id) {
    log_assert(is_round_id($round_id));
    $query = <<<SQL
UPDATE `ia_task`
    JOIN `ia_round_task` ON `ia_round_task`.`task_id` = `ia_task`.`id`
    SET `hidden` = 0
    WHERE `ia_round_task`.`round_id` = '%s'
SQL;
    db_query(sprintf($query, db_escape($round_id)));
}

// Makes all tasks hidden
// No error handling.
// FIXME: task.hidden is stupid, we need proper security
function round_hide_all_tasks($round_id) {
    log_assert(is_round_id($round_id));
    $query = <<<SQL
UPDATE `ia_task`
    JOIN `ia_round_task` ON `ia_round_task`.`task_id` = `ia_task`.`id`
    SET `hidden` = 1
    WHERE `ia_round_task`.`round_id` = '%s'
SQL;
    db_query(sprintf($query, db_escape($round_id)));
}

// FIXME: horrible evil hack, for the eval.
// FIXME: replace with eval queue
// Gets the round to start, or null.
function round_get_round_to_start() {
    // Build duration subquery.
    $duration_subquery = <<<SQL
SELECT `value` FROM `ia_parameter_value`
    WHERE `object_type` = 'round' AND
          `object_id` = `id` AND
          `parameter_id` = 'duration'
    LIMIT 1
SQL;

    $query = <<<SQL
SELECT * FROM `ia_round`
    WHERE `state` != 'running' AND 
    `start_time` <= '%s' AND 
    DATE_ADD(`start_time`, INTERVAL ($duration_subquery) HOUR) > '%s'
    LIMIT 1
SQL;
    return db_fetch(sprintf($query, db_date_format(), db_date_format()));
}

// FIXME: horrible evil hack, for the eval.
// FIXME: replace with eval queue
// Gets the round to stop, or null.
// Duration is in the params, so we join. FUCK YEAH!!!
function round_get_round_to_stop() {
    // Build duration subquery.
    $duration_subquery = <<<SQL
SELECT `value` FROM `ia_parameter_value`
    WHERE `object_type` = 'round' AND
          `object_id` = `id` AND
          `parameter_id` = 'duration'
    LIMIT 1
SQL;

    // Build the main query.
    $query = <<<SQL
SELECT *
    FROM `ia_round`
    WHERE DATE_ADD(`start_time`, INTERVAL ($duration_subquery) HOUR) <= '%s'
          AND `state` != 'complete'
    LIMIT 1
SQL;
    return db_fetch(sprintf($query, db_date_format()));
}

// FIXME: horrible evil hack, for the eval.
// FIXME: replace with eval queue
// Gets the round to put in waiting, or null.
// This is to prevent "back to the future" situations from fucking up round registration
function round_get_round_to_wait() {
    // Build duration subquery.
    $duration_subquery = <<<SQL
SELECT `value` FROM `ia_parameter_value`
    WHERE `object_type` = 'round' AND
          `object_id` = `id` AND
          `parameter_id` = 'duration'
    LIMIT 1
SQL;

    // Build the main query.
    $query = <<<SQL
SELECT *
    FROM `ia_round`
    WHERE `start_time` > '%s' AND `state` != 'waiting'
    LIMIT 1
SQL;
    return db_fetch(sprintf($query, db_date_format()));
}

?>
