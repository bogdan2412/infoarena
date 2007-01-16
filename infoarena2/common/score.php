<?php

require_once(IA_ROOT . "common/db/score.php");
require_once(IA_ROOT . "common/db/round.php");
require_once(IA_ROOT . "common/db/task.php");

function score_update_for_job($score, $time, $user_id, $task_id)
{
    $rounds = task_get_parent_rounds($task_id);
    foreach ($rounds as $round_id) {
        //FIXME: Improve this
        if (round_is_registered($round_id, $user_id)) {
            $round = round_get($round_id);
            if ($round['state'] == 'running') {
                score_update('score', $user_id, $task_id, $round_id, $score);
            }
        }
    }
}

?>
