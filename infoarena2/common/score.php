<?php

require_once(IA_ROOT . "common/db/score.php");

function score_update_for_job($score, $time, $user_id, $task_id)
{
    score_update('score', $user_id, $task_id, 'arhiva', $score);
}

?>
