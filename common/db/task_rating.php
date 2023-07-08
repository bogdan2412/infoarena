<?php

require_once(Config::ROOT."common/db/db.php");
require_once(Config::ROOT."common/task_rating.php");

function task_rating_get_all($task_id) {
    $query = "SELECT user_id, idea, theory, coding FROM ia_task_ratings
                WHERE task_id = " . db_quote($task_id);

    $res = db_fetch_all($query);

    return $res;
}

// Returns an array with ratings given by $user_id to $task_id
function task_rating_get($task_id, $user_id) {
    log_assert(is_task_id($task_id));
    log_assert(is_user_id($user_id));

    $query = sprintf("SELECT idea, theory, coding FROM ia_task_ratings
                      WHERE task_id = %s AND user_id = %s",
                      db_quote($task_id), db_quote($user_id));

    $result = db_fetch($query);

    if (!$result) {
        $result = array(
              'idea' => null,
              'theory' => null,
              'coding' => null,
        );
    }

    return $result;
}

// Update the rating for task_id
function task_rating_update($task_id) {
    $res = task_rating_get_all($task_id);

    $task_rating = task_rating_compute($res);

    // Update the rating in db
    $values = array(
        'rating' => $task_rating
    );
    $where = "id = " . db_quote($task_id);

    db_update('ia_task', $values, $where);
}

// Inserts new ratings for task_id by user user_id
// ratings are specified in $ratings
function task_rating_add($task_id, $user_id, $ratings) {
    $values = array(
        'task_id' => $task_id,
        'user_id' => $user_id,
        'idea' => $ratings['idea'],
        'theory' => $ratings['theory'],
        'coding' => $ratings['coding']
    );

    $where = "task_id = " . db_quote($task_id) . " AND user_id = " .
                db_quote($user_id);


    if (!db_query_value("SELECT COUNT(*) FROM ia_task_ratings WHERE $where")) {
        db_insert('ia_task_ratings', $values);
    } else {
        db_update('ia_task_ratings', $values, $where);
    }

    task_rating_update($task_id);
}

?>
