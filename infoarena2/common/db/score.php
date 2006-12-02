<?php

require_once(IA_ROOT."common/db/db.php");

// Update a score.
// user/task/round can be null.
function score_update($rank_id, $user_id, $task_id, $round_id, $value)
{
    log_assert($rank_id);
    $query = sprintf("
            INSERT INTO ia_score (`id`, `score`, `user_id`, `task_id`, `round_id`)
            VALUES ('%s', %s, %s, %s, %s) ON DUPLICATE KEY UPDATE `id`=`id`",
            db_escape($rank_id), $value,
            ($user_id === null ? 'NULL' : $user_id),
            ($task_id === null ? 'NULL' : "'".db_escape($task_id)."'"),
            ($round_id === null ? 'NULL' : "'".db_escape($round_id)."'"));
    //log_print($query);
    return db_query($query);
}

function db_escape_array($array)
{
    $ret = '';
    foreach ($array as $element) {
        if ($ret) {
            $ret .= ", ";
        }
        $ret .= "'" . db_escape($element) . "'";
    }
    return $ret;
}

// Builds a where clause for a score query.
// Returns an array of conditions; you should do something like
// join($where, ' AND ');
function score_build_where_clauses($user, $task, $round)
{
    $where = array();

    if ($user != null) {
        if (is_array($user) && count($user) > 0) {
            $where[] = "(`user_id` IN (" . db_escape_array($user) . "))";
        } else if (is_string($user)) {
            $where[] = sprintf("(`user_id` == '%s')", $user);
        }
    }
    if ($task != null) {
        if (is_array($task) && count($task) > 0) {
            $where[] = "(`task_id` IN (" . db_escape_array($task) . "))";
        } else if (is_string($task)) {
            $where[] = sprintf("(`task_id` = '%s')", $task);
        }
    }
    if ($round != null) {
        if (is_array($round) && count($round) > 0) {
            $where[] = "(`round_id` IN (" . db_escape_array($round) . "))";
        } else if (is_string($round)) {
            $where[] = sprintf("(`round_id` = '%s')", $round);
        }
    }

    return $where;
}

// Build a query for a certain score.
// Can be used as a subquery.
function score_build_query($score, $user, $task, $round)
{
    $cond = score_build_where_clauses($user, $task, $round);
    $cond[] = "(id = '".db_escape($score['name'])."')";
    $query = "SELECT SUM(score) FROM ia_score WHERE " . implode(" AND ", $cond);
               
}

// Get scores.
// $user, $task, $round can be null, string or an array.
// If null it's ignored, otherwise only scores for those users/tasks/rounds are counted.
function score_get($score_id, $user, $task, $round, $start, $count, $groupby = "user_id")
{
    log_assert($score_id !== null);
    $where = score_build_where_clauses($user, $task, $round);
    $where[] = sprintf("ia_score.`id` = '%s'", db_escape($score_id));
    $query = sprintf("
            SELECT SQL_CALC_FOUND_ROWS
                ia_score.`id` as `rank_id`, `user_id`, `task_id`, `round_id`, SUM(`score`) as score, 
                ia_user.username as user_name, ia_user.full_name as user_full
            FROM ia_score 
                LEFT JOIN ia_user ON ia_user.id = ia_score.user_id
            WHERE %s GROUP BY %s 
            ORDER BY `score` DESC LIMIT %s, %s",
            join($where, " AND "), $groupby, $start, $count);
    $scores = db_fetch_all($query);
    
    return array(
            'scores' => $scores,
            'total_rows' => db_query_value("SELECT FOUND_ROWS();"),
    );
}

?>
