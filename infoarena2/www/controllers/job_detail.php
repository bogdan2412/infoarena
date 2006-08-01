<?php
function controller_job_detail($suburl) {
    $job_id = getattr($_GET, 'id');
    $job = job_get_by_id($job_id);

    $view['title'] = 'Detalii despre job ' . $job_id;
    $view['job'] = $job;
    execute_view('views/job_detail.php', $view);
}
?>