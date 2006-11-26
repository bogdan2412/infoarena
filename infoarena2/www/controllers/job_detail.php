<?php

require_once(IA_ROOT . "common/db/job.php");

function controller_job_detail($suburl) {
    $job_id = getattr($_GET, 'id');
    $job = job_get_by_id($job_id);

    $view['title'] = 'Detalii despre job ' . $job_id;
    $view['job'] = $job;

    if (!$view['job']['eval_message']) {
        $view['job']['eval_message'] = "&nbsp";
    }
    execute_view('views/job_detail.php', $view);
}

?>
