<?php

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

// Get scores.
// $user_id, $task_id, $round_id can be null.
function score_get($rank_id, $user_id, $task_id, $round_id, $start, $count, $groupby = "user_id")
{
    $where = array();
    if ($user_id != null) {
        $where[] = sprintf("`user_id` == '%s'", $user_id);
    }
    if ($task_id != null) {
        $where[] = sprintf("`task_id` == '%s'", $task_id);
    }
    if ($round_id != null) {
        $where[] = sprintf("`round_id` == '%s'", $round_id);
    }
    log_assert($rank_id !== null);
    $where[] = sprintf("`rank_id` == '%s'", $rank_id);
    $query = sprintf("
            SELECT `id` as `rank_id`, `user_id`, `task_id`, `round_id`, SUM(`round_id`)
            FROM ia_score
            WHERE %s GROUP BY %s LIMIT %s, %s",
            join($where, " AND "), $groupby, $start, $count);
}

?>
