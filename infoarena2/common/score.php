<?php

require_once(IA_ROOT . "common/db/score.php");
require_once(IA_ROOT . "common/db/task.php");

function score_update_for_job($score, $time, $user_id, $task_id)
{
    $rounds = task_get_parent_rounds($task_id);
    foreach ($rounds as $round) {
        //FIXME: Decide whether to update score or not... some day..
        score_update('score', $user_id, $task_id, $round, $score);
    }
}

?>
