<?php

require_once(Config::ROOT."common/db/db.php");
require_once(Config::ROOT."common/db/parameter.php");
require_once(Config::ROOT."common/db/round_task.php");

// Get round
function round_get($round_id) {
    if (!$round_id) {
        return null;
    }

    // this assert breaks templates pages with round_id = %round_id%
    log_assert(is_round_id($round_id));

    $query = sprintf("SELECT * FROM ia_round WHERE `id` = %s",
                     db_quote($round_id));
    return db_fetch($query);
}

// Create new round
// Return success.
function round_create($round, $round_params, $user_id, $remote_ip_info = null) {
    log_assert(is_user_id($user_id));
    log_assert_valid(round_validate($round));
    log_assert_valid(round_validate_parameters($round['type'], $round_params));

    db_insert('ia_round', $round);
    $new_round = round_get($round['id']);

    if ($new_round) {
        round_update_parameters($round['id'], $round_params);

        // Copy templates.
        require_once(Config::ROOT . "common/textblock.php");
        $replace = array("round_id" => $round['id']);
        textblock_copy_replace("template/newround", $round['page_name'],
                $replace, "round: {$round['id']}", $user_id, $remote_ip_info);

        return true;
    } else {
        return false;
    }
}

function round_recompute_score($round_id) {
    db_query("DELETE FROM ia_score_user_round
        WHERE round_id = ".db_quote($round_id));
    $query = "SELECT SUM(`score`) AS score, user_id
        FROM ia_score_user_round_task
        WHERE `round_id` = ".db_quote($round_id)."
        GROUP BY `user_id`";
    $rows = db_fetch_all($query);
    if (empty($rows)) {
        return false;
    }
    $query = "INSERT INTO `ia_score_user_round`
        (`user_id`, `round_id`, `score`)
        VALUES ";
    $first = true;
    foreach ($rows as $row) {
        $user_id = $row['user_id'];
        $score = $row['score'];

        if (!$first) {
            $query .= ", ";
        } else {
            $first = false;
        }
        $query .= sprintf("(%s, %s, %s)",
            db_quote($user_id), db_quote($round_id), db_quote($score));
    }
    db_query($query);
}

// Update a round.
function round_update($round) {
    log_assert_valid(round_validate($round));
    db_update('ia_round', $round,
              "`id` = '".db_escape($round['id'])."'");
}

// Returns array with all tasks attached to the specified round
//
// :WARNING: This does not select all fields related to each task, but rather
// chooses a few.  Make sure that Identity has all necessary information to
// yield a correct answer.
//
// FIXME: sensible ordering.
// FIXME: cache tasks.
//
// if user_id is non-null a join is done on $score
function round_get_tasks($round_id, $first = 0, $count = null,
                         $user_id = null, $fetch_scores = false,
                         $filter = null, $progress = false,
                         $order_by_solved = null) {
    if ($count === null) {
        $count = 666013;
    }
    $fields = "round_task.task_id AS id, ".
              "round_task.`order_id` AS `order`, ".
              "task.`title` AS `title`, ".
              "task.`page_name` AS `page_name`, ".
              "task.`source` AS `source`, ".
              "task.`security` AS `security`, ".
              'task.`type` AS `type`,
               task.`open_source` AS `open_source`,
               task.`open_tests` AS `open_tests`,
               task.`rating` AS `rating`,
               task.`solved_by` AS `solved_by`';
    if (is_null($order_by_solved) || $order_by_solved === 'no') {
        $order = 'ORDER BY round_task.`order_id`';
    } else if ($order_by_solved === 'asc') {
        $order = 'ORDER BY `solved_by` ASC';
    } else if ($order_by_solved == 'desc') {
        $order = 'ORDER BY `solved_by` DESC';
    } else {
        $order = 'ORDER BY round_task.`order_id`';
    }
    if ($user_id == null || $fetch_scores == false) {
        $query = sprintf("SELECT $fields
                          FROM ia_round_task as round_task
                          LEFT JOIN ia_task as task ON task.id = round_task.task_id
                          WHERE `round_task`.`round_id` = '%s'
                          $order LIMIT %d, %d",
                          db_escape($round_id), db_escape($first), db_escape($count));
    } else {
        $filter_clause = db_get_task_filter_clause($filter, 'score');
        log_assert(is_whole_number($user_id));
        $query = sprintf("SELECT $fields, score.`score` AS `score`
                          FROM ia_round_task as round_task
                          LEFT JOIN ia_task as task ON task.id = round_task.task_id
                          LEFT JOIN ia_score_user_round_task as score ON
                                score.round_id = round_task.round_id AND
                                score.task_id = round_task.task_id AND
                                score.user_id = '%s'
                          WHERE `round_task`.`round_id` = '%s'
                          AND %s
                          $order LIMIT %d, %d",
                         db_escape($user_id),
                         db_escape($round_id), db_escape($filter_clause),
                         db_escape($first), db_escape($count));
    }

    $res = db_fetch_all($query);

    // Check if we have what to progress
    if ($progress && count($res) > 0) {
        $task_ids = array();
        foreach ($res as $row) {
            $task_ids[] = $row['id'];
        }

        $query_ratings = sprintf(
              "SELECT task_ratings.task_id AS id, count(*) AS rating_count
               FROM ia_task_ratings AS task_ratings
               WHERE task_ratings.task_id IN (%s)
               GROUP BY id",
               implode(',', array_map('db_quote', $task_ids))
        );

        $res_ratings = db_fetch_all($query_ratings);

        $rating_count = array();
        foreach ($res_ratings as $res_rating) {
            $rating_count[$res_rating['id']] = $res_rating['rating_count'];
        }

        foreach ($res as &$row) {
            $row['progress'] = getattr($rating_count, $row['id']);
        }
    }

    return $res;
}

function round_get_task_count($round_id, $user_id, $filter) {
    if ($user_id && $filter) {
        $filter_clause = db_get_task_filter_clause($filter, 'ia_score_user_round_task');
        $query = sprintf("SELECT COUNT(*) FROM ia_round_task " .
                         "LEFT JOIN ia_score_user_round_task " .
                         "ON ia_round_task.round_id = ia_score_user_round_task.round_id " .
                         "AND ia_round_task.task_id = ia_score_user_round_task.task_id " .
                         "AND ia_score_user_round_task.user_id = %s " .
                         "WHERE ia_round_task.round_id = '%s' " .
                         "AND %s",
                         db_escape($user_id),
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
// Returns true if success, false otherwise
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $tasks is array of task id's
function round_update_task_list($round_id, $old_tasks, $tasks,
        $force_update_security = false, $force_check_common_tasks = false) {
    log_assert(is_round_id($round_id));

    $old_tasks_count = count($old_tasks);

    // Do nothing with common tasks, be smart.
    $common_tasks = array_intersect($old_tasks, $tasks);
    $old_tasks = array_diff($old_tasks, $common_tasks);
    $tasks = array_diff($tasks, $common_tasks);

    // Either succeed or do nothing at all
    db_query('START TRANSACTION');

    // delete round-task relations
    if (count($old_tasks) > 0) {
        $query = sprintf("DELETE FROM ia_round_task
                          WHERE round_id = %s AND task_id IN (%s)",
                         db_quote($round_id),
                         implode(',', array_map("db_quote", $old_tasks)));
        db_query($query);
    }

    foreach ($old_tasks as $task) {
        // Update parent round cache for old tasks
        task_get_parent_rounds($task);
        if ($force_update_security) {
            task_update_security($task, 'check');
        }
    }

    // Also check common tasks when forced to
    if ($force_update_security && $force_check_common_tasks
        && count($common_tasks) > 0) {
        foreach ($common_tasks as $task) {
            task_update_security($task, 'check');
        }
    }

    $result = true;
    if (count($tasks) > 0) {
        // insert new relations
        $values = array();
        $order_id = $old_tasks_count;
        foreach ($tasks as $task_id) {
            $order_id += 1;
            $values[] = "('".db_escape($round_id)."', '".
                        db_escape($task_id)."', '".db_escape($order_id)."')";
        }

        $query = "INSERT INTO ia_round_task (round_id, task_id, order_id)
                  VALUES ". implode(', ', $values);
        // This query fails pretty often for some reason
        $result = db_query_retry($query, 1);
    }

    // Commit transaction
    if ($result) {
        foreach ($tasks as $task) {
            // Update parent round cache for new tasks
            task_get_parent_rounds($task);
            if ($force_update_security) {
                task_update_security($task, 'check');
            }
        }

        round_task_recompute_order($round_id);

        db_query('COMMIT');
    } else {
        db_query('ROLLBACK');
    }

    return $result;
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

// Unregisters user $user_id from round $round_id
// NOTE: This does not check if user is registered
// Returns false if failed
function round_unregister_user($round_id, $user_id) {
    log_assert(is_round_id($round_id));
    log_assert(is_user_id($user_id));

    $query = sprintf("DELETE FROM `ia_user_round`".
                     "WHERE `user_id` = %s AND `round_id` = %s",
                     db_quote($user_id), db_quote($round_id));

    db_query($query);
    return db_affected_rows() == 1;
}

// Returs list of registred user to round $round_id, order by rating
// round can be
function round_get_registered_users_range($round_id, $start, $range) {
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
                      ORDER BY rating DESC, user.id ASC
                      LIMIT %s, %s", db_escape($round_id), $start, $range);

    $tab = db_fetch_all($query);
    for ($i = 0; $i < count($tab); ++$i) {
        $tab[$i]['position'] = $start + $i + 1;
    }
    return $tab;
}

// Returns number of registered users in a certain round
function round_get_registered_users_count($round_id) {
    log_assert(is_round_id($round_id));

    // FIXME: don't differentiate on $round['type']
    $round = round_get($round_id);
    $query = sprintf("SELECT COUNT(*) FROM ia_user_round
                      WHERE `round_id` = '%s'",
                      db_escape($round_id));
    return db_query_value($query);
}

// Makes all tasks protected from private
// No error handling.
function round_unhide_all_tasks($round_id) {
    log_assert(is_round_id($round_id));
    $tasks = round_get_tasks($round_id);
    foreach ($tasks as $task) {
        task_update_security($task['id'], 'protected');
    }
}

// Makes all tasks private from protected
// No error handling.
function round_hide_all_tasks($round_id) {
    log_assert(is_round_id($round_id));
    $tasks = round_get_tasks($round_id);
    foreach ($tasks as $task) {
        task_update_security($task['id'], 'private');
    }
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
    DATE_ADD(`start_time`, INTERVAL ($duration_subquery) * 60 * 60 SECOND) > '%s'
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
    WHERE DATE_ADD(`start_time`, INTERVAL ($duration_subquery) * 60 * 60 SECOND) <= '%s'
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
    // Build the main query.
    $query = <<<SQL
SELECT *
    FROM `ia_round`
    WHERE `start_time` > '%s' AND `state` != 'waiting'
    LIMIT 1
SQL;
    return db_fetch(sprintf($query, db_date_format()));
}

function round_get_many($options) {
    $field_list = "`ia_round`.*";
    $where = array();
    if (getattr($options, "name_regexp")) {
        $where[] = "`ia_round`.`id` REGEXP " . db_quote($options["name_regexp"]);
    }
    if (getattr($options, "type_regexp")) {
        $where[] = "`ia_round`.`type` REGEXP " . db_quote($options["type_regexp"]);
    }

    // Add a join for username.
    $join = "";
    if (getattr($options, 'username', false) == true) {
        $field_list .= ", `ia_user`.`username` AS `user_name`" .
                       ", `ia_user`.`full_name` AS `user_fullname`" .
                       ", `ia_user`.`rating_cache` AS `user_rating`";
        $join = "LEFT JOIN ia_user ON `ia_round`.`user_id` = `ia_user`.`id`";
    }

    if (strtolower(getattr($options, "order", "desc") == "desc")) {
        $order = "DESC";
    } else {
        $order = "ASC";
    }

    $limit = db_quote(getattr($options, "limit", 50));
    $offset = db_quote(getattr($options, "offset", 0));

    if (!empty($where)) {
        $where = " WHERE (" . implode(") AND (", $where) . ")";
    } else {
        $where = "";
    }

    $query = "SELECT $field_list FROM `ia_round` $join $where";
    $query .= " ORDER BY `ia_round`.`start_time` $order, `ia_round`.`id` $order";
    $query .= " LIMIT $offset, $limit";

    $rounds = db_fetch_all($query);

    if (getattr($options, "get_count")) {
        $query = "SELECT COUNT(*) FROM `ia_round` $where";
        $rounds["count"] = db_fetch($query);
        $rounds["count"] = array_pop($rounds["count"]);
    }

    return $rounds;
}

function round_delete($round_id) {
    log_assert(is_round_id($round_id));

    // Delete job_tests
    $query = sprintf("SELECT `id`
                      FROM `ia_job`
                      WHERE `round_id` = %s",
                      db_quote($round_id));

    $job_ids_fetched = db_fetch_all($query);

    $job_ids = array();
    foreach ($job_ids_fetched as $job) {
        $job_ids[] = (int)$job["id"];
    }

    if (count($job_ids)) {
        $formated_job_ids = implode(", ", array_map("db_quote", $job_ids));
        $query = sprintf("DELETE FROM `ia_job_test`
                          WHERE `job_id` IN (%s)",
                          $formated_job_ids);
        db_query($query);

        $query = sprintf("DELETE FROM `ia_job`
                          WHERE `id` IN (%s)",
                          $formated_job_ids);
        db_query($query);
    }

    // Delete entries from ia_parameter_value...
    $query = sprintf("DELETE FROM `ia_parameter_value`
                      WHERE `object_type` = 'round'
                        AND `object_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // Delete entries from round-task
    $query = sprintf("DELETE FROM `ia_round_task`
                      WHERE `round_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // Delete entries from user-round
    $query = sprintf("DELETE FROM `ia_user_round`
                      WHERE `round_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // Delete entries from round-task
    $query = sprintf("DELETE FROM `ia_round_task`
                      WHERE `round_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // Delete entries from ia_score_user_round
    $query = sprintf("DELETE FROM `ia_score_user_round`
                      WHERE `round_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // Delete entries from ia_score_user_round_task
    $query = sprintf("DELETE FROM `ia_score_user_round_task`
                      WHERE `round_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // Delete entries from ia_rating
    $query = sprintf("DELETE FROM `ia_rating`
                      WHERE `round_id` = %s",
                      db_quote($round_id));
    db_query($query);

    // ACTUALLY DELETE THE ROUND
    $query = sprintf("DELETE FROM `ia_round`
                      WHERE `id` = %s",
                      db_quote($round_id));
    db_query($query);
}
