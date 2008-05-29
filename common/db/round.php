<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/parameter.php");

function _round_cache_add($round) {
    mem_cache_set("round-by-id:{$round['id']}", $round, IA_MEM_CACHE_ROUND_EXPIRATION);
    return $round;
}

function _round_cache_delete($round) {
    mem_cache_delete("round-by-id:{$round['id']}");
}

// Get round
function round_get($round_id) {
    // this assert brakes templates pages with round_id = %round_id%
    log_assert(is_round_id($round_id));

    if (($res = mem_cache_get("round-by-id:$round_id")) !== false) {
        return $res;
    }

    $query = sprintf("SELECT * FROM ia_round WHERE `id` = %s",
                     db_quote($round_id));
    return _round_cache_add(db_fetch($query));
}

// Create new round
// Return success.
function round_create($round, $round_params, $user_id) {
    log_assert(is_user_id($user_id));
    log_assert_valid(round_validate($round));
    log_assert_valid(round_validate_parameters($round['type'], $round_params));

    db_insert('ia_round', $round);
    _round_cache_delete($round);
    $new_round = round_get($round['id']);

    if ($new_round) {
        round_update_parameters($round['id'], $round_params);

        // Copy templates.
        require_once(IA_ROOT_DIR . "common/textblock.php");
        $replace = array("round_id" => $round['id']);
        textblock_copy_replace("template/newround", $round['page_name'],
                $replace, "round: {$round['id']}", $user_id);

        _round_cache_add($round);
        return true;
    } else {
        _round_cache_delete($round);
        return false;
    }
}

// Update a round.
function round_update($round) {
    log_assert_valid(round_validate($round));
    if (db_update('ia_round', $round,
            "`id` = '".db_escape($round['id'])."'")) {
        _round_cache_add($round);
    } else {
        _round_cache_delete($round);
    }
}

// Returns array with all tasks attached to the specified round
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
//
// FIXME: sensible ordering.
// FIXME: cache tasks.
//
// if user_id is non-null a join is done on $score
function round_get_tasks($round_id, $first = 0, $count = null, $user_id = null, $score_name = null, $filter = null) {
    if ($count === null) {
        $count = 666013;
    }
    $fields = "round_task.task_id AS id, ".
              "task.`order` AS `order`, ".
              "task.`title` AS `title`, ".
              "task.`author` AS `author`, ".
              "task.`page_name` AS `page_name`, ".
              "task.`source` AS `source`, ".
              "task.`hidden` AS `hidden`, ".
              "task.`type` AS `type`,
               task.`open_source` AS `open_source`,
               task.`open_tests` AS `open_tests`";

    if ($score_name === null || $user_id === null) {
        $query = sprintf("SELECT $fields
                          FROM ia_round_task as round_task
                          LEFT JOIN ia_task as task ON task.id = round_task.task_id
                          WHERE `round_task`.`round_id` = '%s'
                          ORDER BY task.`order` LIMIT %d, %d",
                         db_escape($round_id), db_escape($first), db_escape($count));
    } else {
        $filter_clause = db_get_task_filter_clause($filter, 'score');
        log_assert(is_whole_number($user_id));
        $query = sprintf("SELECT $fields, score.score as score
                          FROM ia_round_task as round_task
                          LEFT JOIN ia_task as task ON task.id = round_task.task_id
                          LEFT JOIN ia_score as score ON
                                score.user_id = %s AND
                                score.name = '%s' AND
                                score.round_id = '%s' AND
                                score.task_id = round_task.task_id
                          WHERE `round_task`.`round_id` = '%s'
                              AND %s
                          ORDER BY task.`order` LIMIT %d, %d",
                         db_escape($user_id), db_escape($score_name), db_escape($round_id),
                         db_escape($round_id), db_escape($filter_clause),
                         db_escape($first), db_escape($count));
    }
    return db_fetch_all($query);
}

function round_get_task_count($round_id, $user_id, $scores, $filter)
{
    if ($user_id && $filter && $scores) {
        $filter_clause = db_get_task_filter_clause($filter, 'ia_score');
        $query = sprintf("SELECT COUNT(*) FROM ia_round_task " .
                         "LEFT JOIN ia_score " .
                         "ON ia_round_task.round_id = ia_score.round_id " .
                         "AND ia_round_task.task_id = ia_score.task_id " .
                         "AND ia_score.user_id = %s " .
                         "AND ia_score.name = '%s' " .
                         "WHERE ia_round_task.round_id = '%s' " .
                         "AND %s",
                         db_escape($user_id),
                         db_escape($scores),
                         db_escape($round_id),
                         db_escape($filter_clause)
                         );
    } else {
        $query = sprintf("SELECT COUNT(*) FROM ia_round_task
                         WHERE `round_id` = '%s'",
                         db_escape($round_id));
    }
    return db_query_value($query);
}

// Get round parameters.
// array() if nothing found?
function round_get_parameters($round_id) {
    return parameter_get_values('round', $round_id);
}

// binding for parameter_update_values
function round_update_parameters($round_id, $param_values) {
    parameter_update_values('round', $round_id, $param_values);
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

    log_print_r($tasks);
    log_print(count($tasks));

    if (count($tasks) > 0) {
        // insert new relations
        $values = array();
        foreach ($tasks as $task_id) {
            $values[] = "('".db_escape($round_id)."', '".db_escape($task_id)."')";
        }
        $query = "INSERT INTO ia_round_task (round_id, task_id) 
                  VALUES ". implode(', ', $values);
        db_query($query);
    }
}

// Returns boolean whether given user is registered to round $round_id
function round_is_registered($round_id, $user_id) {
    log_assert(is_round_id($round_id));
    log_assert(is_user_id($user_id));

    $query = sprintf("SELECT COUNT(*) AS `cnt` FROM ia_user_round
                      WHERE round_id=%s AND user_id=%s",
                     db_quote($round_id), db_quote($user_id));

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
    log_assert(is_user_id($user_id));
    $insert_fields = array(
        "round_id" => $round_id,
        "user_id" => $user_id
    );
    return db_insert('ia_user_round', $insert_fields);
}

// Returs list of registred user to round $round_id, order by rating
// round can be 
function round_get_registered_users_range($round_id, $start, $range)
{
    log_assert(is_round_id($round_id));
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert($start >= 0);
    log_assert($range >= 0);

    // FIXME: don't differentiate on $round['type']
    $round = round_get($round_id);
    $query = sprintf("SELECT user.id AS user_id, user.rating_cache AS rating,
                      user.username AS username, user.full_name AS fullname
                      FROM ia_user_round AS user_round
                      LEFT JOIN ia_user AS user ON user_id = user.id
                      WHERE round_id = '%s'
                      ORDER BY rating DESC
                      LIMIT %s, %s", db_escape($round_id), $start, $range);

    $tab = db_fetch_all($query);
    for ($i = 0; $i < count($tab); ++$i) {
        $tab[$i]['position'] = $start + $i + 1;
    }
    return $tab;
}

// Returns number of registered users in a certain round
function round_get_registered_users_count($round_id)
{
    log_assert(is_round_id($round_id));

    // FIXME: don't differentiate on $round['type']
    $round = round_get($round_id);
    $query = sprintf("SELECT COUNT(*) FROM ia_user_round
                      WHERE `round_id` = '%s'",
                      db_escape($round_id));
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
