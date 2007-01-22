<?php

require_once(IA_ROOT . "common/db/score.php");
require_once(IA_ROOT . "common/db/round.php");
require_once(IA_ROOT . "common/db/task.php");

function score_update_for_job($score, $time, $user_id, $task_id)
{
    $rounds = task_get_parent_rounds($task_id);
    foreach ($rounds as $round_id) {
        $round = round_get($round_id);
        round_event_job_score($round, $score, $time, $user_id, $task_id);
    }
}

function round_event_job_score($round, $score, $time, $user_id, $task_id) {
    $rparams = round_get_parameters($round['id']);
    $time = db_date_parse($time);
    $rstart = db_date_parse($round['start_time']);
    $rduration = getattr($rparams, 'duration', 10000000) * 60 * 60;
    if ($time >= $rstart && $time <= $rstart + $rduration) {
        score_update('score', $user_id, $task_id, $round['id'], $score);
    }
}

?>
