<?php

require_once Config::ROOT.'common/db/db.php';
require_once Config::ROOT.'common/db/round.php';
require_once Config::ROOT.'common/parameter.php';
require_once Config::ROOT.'common/rating.php';
require_once Config::ROOT.'common/cache.php';

// Updates a user's rating and deviation
function score_update_rating($user_id, $round_id, $deviation, $rating) {
    $query = 'INSERT INTO `ia_rating`
             (`user_id`, `round_id`, `deviation`, `rating`)
             VALUES ('.implode(',',
             array(db_quote($user_id),
                 db_quote($round_id),
                 db_quote($deviation),
                 db_quote($rating),
             )).') ON DUPLICATE KEY UPDATE '.
                 '`rating` = '.$rating.',
                 `deviation` = '.$deviation;
    db_query($query);
    return db_affected_rows();
}

// Updates user's task score
function score_update($user_id, $task_id, $round_id, $value) {
    log_assert(is_user_id($user_id), "Bad user id '$user_id'");
    log_assert(is_task_id($task_id), "Bad task id '$task_id'");
    log_assert(is_round_id($round_id), "Bad round id '$round_id'");

    // Add user_id score for task_id at round_id to cache
    mem_cache_set('user-task-round:'.$user_id.'-'.$task_id.'-'.$round_id,
                  (int)$value);

    // Also update user-task-max-score if it's in the cache
    $cache_key = 'user-task-last-score:'.$user_id.'-'.$task_id;
    if (($res = mem_cache_get($cache_key)) != false) {
        if ($value > $res) {
            mem_cache_set($cache_key, $value);
        }
    }

    // Update user_id score for task_id at round_id
    $add_incorrect = ($value == 100) ? 0 : 1;
    $query = "INSERT INTO `ia_score_user_round_task`
            (`user_id`, `round_id`, `task_id`, `score`)
            VALUES (".implode(',',
            array(db_quote($user_id),
                db_quote($round_id),
                db_quote($task_id),
                db_quote($value),
            )).") ON DUPLICATE KEY UPDATE
                `score` = ".db_quote($value).",
                `incorrect_submits` = `incorrect_submits` + $add_incorrect
    ";
    db_query($query);

    // update score for round_id
    $subquery = "
            ( SELECT SUM(`score`) AS 'score' FROM `ia_score_user_round_task`
            WHERE
                `round_id` = ".db_quote($round_id)." &&
                `user_id` = ".db_quote($user_id)."
            GROUP BY `user_id` )";

    $query = 'INSERT INTO `ia_score_user_round` (`user_id`, `round_id`, `score`)
            VALUES ('.implode(',',
            array(db_quote($user_id),
                db_quote($round_id),
                $subquery,
            )).') ON DUPLICATE KEY UPDATE
                `score` = '.$subquery;
    db_query($query);
}

// Builds a where clause for a score query.
// Returns an array of conditions; you should do something like
// join($where, ' AND ');
function score_build_where_clauses($user, $task, $round) {
    $where = array();

    if ($user != null) {
        if (is_array($user) && count($user) > 0) {
            $where[] = '(`user_id` IN ('.db_escape_array($user).'))';
        } else if (is_string($user)) {
            $where[] = sprintf("(`user_id` == '%s')", $user);
        }
    }
    if ($task != null) {
        if (is_array($task) && count($task) > 0) {
            $where[] = '(`task_id` IN ('.db_escape_array($task).'))';
        } else if (is_string($task)) {
            $where[] = sprintf("(`task_id` = '%s')", $task);
        }
    }
    if ($round != null) {
        if (is_string($round)) {
            $round = array($round);
        }

        if (is_array($round)) {
            if (count($round) > 0) {
                $where[] = '(`round_id` IN ('.db_escape_array($round).'))';
            } else {
                $where[] = '(TRUE = FALSE)';
            }
        }
    }

    return $where;
}

// Count function to be used for score_get_rankings
function score_get_count($user, $task, $round) {
    $where = score_build_where_clauses($user, $task, $round);
    if (count($where) == 0) {
        return 0;
    }
    $query = sprintf('SELECT COUNT(DISTINCT user_id) AS `cnt` ' .
                     'FROM ia_score_user_round ' .
                     'WHERE %s',
                     implode(' AND ', $where));
    $res = db_fetch($query);
    return $res['cnt'];
}

// Return rating history for given user_id (not username).
// Output array format is:
//  array(
//      round_id =>
//          array(
//              rating => (int)
//              deviation => (int)
//              timestamp => (int UNIX timestamp)
//              round_page_name => (string)
//              round_title => (string)
//          ),
//      ...
//  );
// Rounds are ordered in chronological order.
function rating_history($user_id) {
    log_assert(is_whole_number($user_id));

    // get round list, chronologically ordered
    $history = rating_rounds();

    // get user scores
    $query = sprintf("SELECT * FROM `ia_rating`
                      LEFT JOIN ia_round ON round_id = ia_round.id
                      WHERE ia_rating.user_id = '%s'
                            AND ia_round.state = 'complete'
                     ", db_escape($user_id));
    $rows = db_fetch_all($query);

    foreach ($rows as $row) {
        $round_id = $row['round_id'];
        $history[$round_id]['rating'] = $row['rating'];
        $history[$round_id]['deviation'] = $row['deviation'];
    }

    // filter out rows which user has not participated in
    foreach (array_keys($history) as $round_id) {
        if (!isset($history[$round_id]['rating'])) {
            unset($history[$round_id]);
        }
    }

    // pretty much done
    return $history;
}

// Returns all completed, rated rounds whose ratings have not yet been
// applied.
function applicable_rating_rounds() {
    $rounds = rating_rounds();

    // filter out rounds having rating_applied on
    $query = "SELECT object_id AS round_id
        FROM `ia_parameter_value`
        WHERE parameter_id = 'rating_applied'
          AND object_type = 'round'
          AND value = '1'
    ";
    $rows = db_fetch_all($query);

    foreach ($rows as $row) {
        // printf("Ignoring already applied round [%s]\n", $row['round_id']);
        unset($rounds[$row['round_id']]);
    }

    return $rounds;
}

// Returns all COMPLETED rounds in chronological order that have ratings
// enabled.
//
// Output array format is:
//  array(
//      round_id =>
//          array(
//              timestamp => (int UNIX timestamp)
//              round_page_name => (string)
//              round_title => (string)
//          ),
//      ...
//  );
function rating_rounds() {
    $query = "SELECT
               object_id AS round_id, `value` AS `timestamp`,
               ia_round.page_name AS round_page_name,
               ia_round.title AS round_title
        FROM `ia_parameter_value`
        LEFT JOIN ia_round ON ia_round.id = ia_parameter_value.object_id
        WHERE parameter_id = 'rating_timestamp' AND object_type = 'round'
              AND NOT ia_round.id IS NULL AND ia_round.`state` = 'complete'
        ORDER BY `timestamp`, round_id
    ";
    $rows = db_fetch_all($query);
    $rounds = array();
    foreach ($rows as $row) {
        $rounds[$row['round_id']] = array(
            'timestamp' => $row['timestamp'],
            'round_page_name' => $row['round_page_name'],
            'round_title' => $row['round_title'],
        );
    }

    // filter out rounds having rating_update off
    $query = "SELECT object_id AS round_id, parameter_id, `value`
        FROM `ia_parameter_value`
        WHERE parameter_id = 'rating_update' AND object_type = 'round'
    ";
    $rows = db_fetch_all($query);
    foreach ($rows as $row) {
        $round_id = $row['round_id'];
        if (!isset($rounds[$round_id])) {
            log_warn("Round {$round_id} has rating_update but no "
                      ."rating_timestamp parameter!");
            unset($rounds[$round_id]);
            continue;
        }
        $value = parameter_decode($row['parameter_id'], $row['value']);
        if ($value) {
            $rounds[$round_id]['rating_update'] = true;
            continue;
        }

        // Round parameters say round does not affect rating
        unset($rounds[$round_id]);
    }
    foreach ($rounds as $round_id => $round) {
        if (!isset($round['rating_update'])) {
            unset($rounds[$round_id]);
        }
    }

    return $rounds;
}

// Init user array with last rating, deviation & timestamp
// Output array format is:
//  array(
//      username => array(
//                      rating => (int),
//                      deviation => (int),
//                      timestamp => (int)
//                  )
//      ...
//  );
//
// NOTE: This array does not contain users never rated!
//
// WARNING: This function is VERY RESOURCE INTENSIVE! Don't use it in
// normal website operations.
//
// Last user ratings (but no deviation / timestamp) is stored directly in
// table ia_user.
function rating_last_scores() {
    // FIXME: horrible query
    $query = "SELECT
        ia_rating.rating AS `rating`, ia_rating.deviation AS deviation,
               ia_rating.user_id, ia_rating.round_id,
               pv.`value` AS `timestamp`, ia_user.username
        FROM ia_rating
        LEFT JOIN ia_parameter_value AS pv
            ON pv.object_type = 'round' AND pv.object_id = ia_rating.round_id
            AND pv.parameter_id = 'rating_timestamp'
        LEFT JOIN ia_user ON ia_user.id = ia_rating.user_id
        ORDER BY `timestamp` DESC, ia_rating.round_id DESC
    ";
    $rows = db_fetch_all($query);

    $users = array();
    foreach ($rows as $row) {
        $username = $row['username'];
        if (isset($users[$username])) {
            continue;
        }

        $users[$username] = array(
                'rating' => $row['rating'],
                'deviation' => $row['deviation'],
                'timestamp' => $row['timestamp'],
        );
    }

    return $users;
}

// Return current rating distribution based on cached ratings.
// NOTE: $bucket_size refers to the absolute rating stored in database
// (ranging to around ~2500).
//
// Output array format:
//  array(
//      13 => <count>,
//      14 => <count>,
//      20 => <count>,
//      ...
//  );
// Key X corresponds to rating bucket [ x*$bucket_size; $bucket_size )
// NOTE: Some buckets may be missing completely
function rating_distribution($bucket_size) {
    log_assert(is_numeric($bucket_size));
    $query = "SELECT
            COUNT(*) AS `count`,
            FLOOR(rating_cache/{$bucket_size}) AS `bucket`
        FROM ia_user
        WHERE 0 < rating_cache
        GROUP BY `bucket`
        ORDER BY rating_cache
    ";
    $rows = db_fetch_all($query);

    $buckets = array();
    foreach ($rows as $row) {
        $buckets[$row['bucket']] = $row['count'];
    }

    return $buckets;
}

// Get top rated users list.
function get_users_by_rating_range($start, $count, $with_rankings = false) {
    $query = "SELECT *
        FROM ia_user
        WHERE rating_cache > 0
        AND security_level != 'admin'
        ORDER BY rating_cache DESC
        LIMIT %s, %s
    ";
    $query = sprintf($query, $start, $count);
    $rows = db_fetch_all($query);

    if ($with_rankings && count($rows)) {
        $query = sprintf("SELECT `rating_cache` AS rating_cache
                          FROM ia_user
                          WHERE rating_cache > 1.5 + 3 * ROUND(%s/3)
                          AND security_level != 'admin'",
                         $rows[0]['rating_cache']);

        $users_before = db_num_rows(db_query($query));


        $rows[0]['position'] = $users_before + 1;
        $equal_scores = $start - $users_before + 1;
        for ($i = 1; $i < count($rows); ++$i) {
            $last_row = $rows[$i - 1];
            $row = & $rows[$i];
            if (rating_scale($row['rating_cache']) ==
                rating_scale($last_row['rating_cache'])) {
                $row['position'] = $last_row['position'];
                $equal_scores = $equal_scores + 1;
            } else {
                $row['position'] = $last_row['position'] + $equal_scores;
                $equal_scores = 1;
            }
        }
    }

    return $rows;
}

// Count function for get_users_by_rating_range.
function get_users_by_rating_count() {
    $query = "SELECT COUNT(*) AS `cnt`
        FROM `ia_user`
        WHERE `rating_cache` > 0
        AND `security_level` != 'admin'";
    $res = db_fetch($query);
    return $res['cnt'];
}

// Clears ALL user ratings & rating history
function rating_clear() {
    db_query('DELETE FROM ia_rating');
    db_query('UPDATE ia_user SET rating_cache = 0');
    db_query("DELETE FROM ia_parameter_value
                WHERE object_type = 'round'
                  AND parameter_id = 'rating_applied'");
}

// Computes rankings for $rounds
// returns entries from start to count
// if detail_task == true, extra columns for each task will be created
// if detail_round == true, extra columns for each round will be created
// @param $rounds A round ID (string) or an array of round IDs
function score_get_rankings($rounds, $tasks, $start = 0, $count = 999999,
                            $detail_task = false, $detail_round = false) {
    if (is_string($rounds)) {
        $rounds = [ $rounds ];
    }
    $where = score_build_where_clauses(null, null, $rounds);
    if (count($where) == 0) {
        return array();
    }

    // Get the total score for all rounds
    $query = '
        SELECT '.(count($rounds) > 1 ? 'SUM(score) AS score' : 'score').',
                user_id, ia_user.username AS user_name,
                ia_user.full_name AS user_full,
                ia_user.rating_cache AS user_rating
        FROM ia_score_user_round
        LEFT JOIN ia_user ON ia_user.id = ia_score_user_round.user_id
        WHERE'.implode('AND', $where).'
        '.(count($rounds) > 1 ? 'GROUP BY `user_id`' : '').'
        ORDER BY score DESC
        LIMIT '.db_escape($start).', '.db_escape($count);

    $rankings = db_fetch_all($query);
    if (count($rankings) == 0) {
        return $rankings;
    }

    $users = array();
    foreach ($rankings as $ranking) {
        array_push($users, $ranking['user_id']);
    }

    // Further queries concern only the users that are in this rankings page
    $filter_users = score_build_where_clauses($users, null, null);
    $where[] = $filter_users[0];

    // Detailed scores are mapped in an array with the following form
    // Array[user_id][object_id] = user score for object
    // Object can be round or task

    if ($detail_round == true) {
        // Get scores for each round
        $query = 'SELECT round_id, user_id, score
                FROM ia_score_user_round
                WHERE '.implode('AND', $where);
        $scores = db_fetch_all($query);
        $round_scores = array(array());
        foreach ($scores as $score) {
            $user_id = $score['user_id'];
            $round_id = $score['round_id'];
            $rscore = $score['score'];
            $round_scores[$user_id][$round_id] = $rscore;
        }
    }

    if ($detail_task == true) {
        // Get scores for each task
        $query = 'SELECT task_id, user_id, score
                FROM ia_score_user_round_task
                WHERE '.implode('AND', $where);
        $scores = db_fetch_all($query);
        $task_scores = array(array());
        foreach ($scores as $score) {
            $user_id = $score['user_id'];
            $task_id = $score['task_id'];
            $tscore = $score['score'];
            $task_scores[$user_id][$task_id] = $tscore;
        }
    }

    // Compute rank for the first entry
    $top_score = $rankings[0]['score'];
    $where = score_build_where_clauses(null, null, $rounds);
    $query = 'SELECT SUM(score) AS score
                FROM ia_score_user_round
                WHERE '.implode(' AND ', $where).'
                GROUP BY user_id
                HAVING score > '.db_quote($top_score);
    $first_rank = db_num_rows(db_query($query)) + 1;

    // create all entries
    for ($i = 0; $i < count($rankings); $i++) {
        $user_id = $rankings[$i]['user_id'];

        // task columns
        if ($detail_task == true) {
            foreach ($tasks as $task_id) {
                if (isset($task_scores[$user_id][$task_id])) {
                    $score = $task_scores[$user_id][$task_id];
                } else {
                    $score = 0;
                }
                $rankings[$i][$task_id] = $score;
            }
        }

        // round columns
        if ($detail_round == true) {
            foreach ($rounds as $round_id) {
                if (isset($round_scores[$user_id][$round_id])) {
                    $score = $round_scores[$user_id][$round_id];
                } else {
                    $score = 0;
                }
                $rankings[$i][$round_id] = $score;
            }
        }

        if ($i == 0) {
            $rankings[$i]['ranking'] = $first_rank;
            continue;
        }

        // Users with the same score should be on the same rank
        if ($rankings[$i]['score'] == $rankings[$i - 1]['score']) {
            $rankings[$i]['ranking'] = $rankings[$i - 1]['ranking'];
        } else {
            $rankings[$i]['ranking'] = $start + $i + 1;
        }
    }

    return $rankings;
}

/**
 * Inserts information about a task in an acm-round for a specific user
 *
 * @param int $round_id
 * @param string $round_id
 * @param string $task_id
 * @param int $score
 * @param int $submission
 * @param int $penalty
 * @param bool $affects_frozen_scoreboard
 * @return void
 */
function score_update_acm_round($user_id, $round_id, $task_id, $score,
                                $submission, $penalty,
                                $affects_frozen_scoreboard = false) {
    $query = sprintf('SELECT * FROM ia_acm_round
                             WHERE user_id = %s AND
                                   round_id = %s AND
                                   task_id = %s',
                     db_quote($user_id),
                     db_quote($round_id),
                     db_quote($task_id));
    $old_info = db_fetch($query);
    /*
     * If it's the first submission we probably made a reevaluation
     * Otherwise if we had 0 points we should push the new information
     */
    if ($submission == 1 || getattr($old_info, 'score') == 0) {
        $partial_score = getattr($old_info, 'partial_score', 0);
        $partial_penalty = getattr($old_info, 'partial_penalty', 0);
        $partial_submission = getattr($old_info, 'partial_submission', 0);

        if ($affects_frozen_scoreboard) {
            $partial_score = $score;
            $partial_penalty = $penalty;
            $partial_submission = $submission;
        }

        $query = sprintf('REPLACE INTO ia_acm_round
                                  VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s)',
                          db_quote($user_id),
                          db_quote($round_id),
                          db_quote($task_id),
                          db_quote($score),
                          db_quote($penalty),
                          db_quote($submission),
                          db_quote($partial_score),
                          db_quote($partial_penalty),
                          db_quote($partial_submission));
        db_query($query);
    }
}

/**
 * Returns the rankings for an acm-style contest
 *
 * It's by default very detailed, with score per task (penalty)
 *
 * It assumes the round is of type 'acm-round'
 *
 * @param string $round_id
 * @param bool $full_results Whether to show full results or
 *                           the scoreboard before freezing
 * @return array
 */
function score_get_rankings_acm($round_id,
                                $full_results = false,
                                $detail_task = true) {
    $round = round_get($round_id);
    if ($round['type'] != 'acm-round') {
        return array();
    }

    $score_column = 'partial_score';
    $penalty_column = 'partial_penalty';
    $submission_column = 'partial_submission';
    if ($full_results) {
        $score_column = 'score';
        $penalty_column = 'penalty';
        $submission_column = 'submission';
    }

    $query = 'SELECT SUM('.$score_column.') AS score,
                     SUM(CASE WHEN '.$score_column.' > 0 THEN '.
                            $penalty_column.' ELSE 0 END) as penalty,
                     user_id, ia_user.username,
                     ia_user.full_name AS fullname,
                     ia_user.rating_cache AS rating
              FROM ia_acm_round
              LEFT JOIN ia_user ON ia_user.id = ia_acm_round.user_id
              INNER JOIN ia_round_task ON
                ia_round_task.round_id = ia_acm_round.round_id AND
                ia_round_task.task_id = ia_acm_round.task_id
              WHERE ia_acm_round.round_id = '.db_quote($round_id).'
              GROUP BY `user_id`
              ORDER BY score DESC, penalty ASC';
    $rankings = db_fetch_all($query);

    $users = array();
    foreach ($rankings as $ranking) {
        $users[$ranking['user_id']] = true;
    }

    $registered_users = round_get_registered_users_range($round_id, 0,
                            round_get_registered_users_count($round_id));

    foreach ($registered_users as $user) {
        if (!isset($users[$user['user_id']])) { // user registered
                                                // but no submission
            $users[$user['user_id']] = true;

            $user['score'] = 0;
            $user['penalty'] = 0;

            $rankings[] = $user;
        }
    }

    if ($detail_task) {
        $tasks = round_get_tasks($round_id);
        $query = 'SELECT task_id, user_id, '.$score_column.' as score, '.
                         $penalty_column.' as penalty, '.
                         $submission_column.' as submission
                  FROM ia_acm_round WHERE round_id = '.db_quote($round_id);

        $scores = db_fetch_all($query);
        $task_info = array();
        foreach ($scores as $score) {
            $user_id = $score['user_id'];
            $task_id = $score['task_id'];
            if (!isset($task_info[$user_id])) {
                 $task_info[$user_id] = array();
            }
            $task_info[$user_id][$task_id] = $score;
        }

        foreach ($rankings as &$user) {
            $user_id = $user['user_id'];
            foreach ($tasks as $task) {
                $task_id = $task['id'];
                $current_info = getattr(getattr($task_info, $user_id, array()),
                                        $task_id, array());
                $user[$task_id] = array(
                    'score' => getattr($current_info, 'score', 0),
                    'penalty' => getattr($current_info, 'penalty', 0),
                    'submission' => getattr($current_info, 'submission', 0),
                );
            }
        }
    }

    for ($i = 0; $i < count($rankings); ++$i) {
        if ($i == 0 ||
            ($rankings[$i - 1]['score'] != $rankings[$i]['score'] ||
             $rankings[$i - 1]['penalty'] != $rankings[$i]['penalty'])) {
            $rankings[$i]['ranking'] = $i + 1;
        } else {
            $rankings[$i]['ranking'] = $rankings[$i - 1]['ranking'];
        }
    }

    return $rankings;
}

function scores_get_by_user_id_and_round_id($user_id, $round_id) {
    $query = sprintf("SELECT *
                      FROM ia_score_user_round_task
                      WHERE user_id = %s AND round_id = %s",
                     db_quote($user_id), db_quote($round_id));
    $scores = db_fetch_all($query);
    return $scores;
}

function total_score_get_by_user_id_and_round_id($user_id, $round_id) {
    $query = sprintf("SELECT *
                      FROM ia_score_user_round
                      WHERE user_id = %s AND round_id = %s",
                     db_quote($user_id), db_quote($round_id));
    $total_score = db_fetch($query);
    return $total_score;
}
