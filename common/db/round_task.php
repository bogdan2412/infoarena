<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/task.php");

// Updates the `order_id` for a (round, task) pair.
function round_task_update_order_id($round_id, $task_id, $order_id) {
    $query = sprintf(
        "UPDATE ia_round_task
         SET order_id = %s
         WHERE round_id = %s AND task_id = %s",
         db_quote($order_id), db_quote($round_id), db_quote($task_id));

    db_query($query);
}

// Preserve consecutive numbers for `order_id`
function round_task_recompute_order($round_id) {
    $query = "SELECT task_id, order_id FROM ia_round_task
              WHERE round_id = " . db_quote($round_id) .
             "ORDER BY order_id";
    $res = db_fetch_all($query);

    $order_id = 0;
    foreach ($res as $row) {
        $order_id += 1;

        if ($order_id != $row['order_id']) {
            $query = sprintf(
                "UPDATE ia_round_task
                 SET order_id = %s
                 WHERE round_id = %s AND task_id = %s",
                 db_quote($order_id), db_quote($round_id),
                 db_quote($row['task_id']));
            db_query($query);

            // update forum if necessary
            if ($round_id == 'arhiva') {
                task_update_forum_topic($row['task_id']);
            }
        }
    }
}
