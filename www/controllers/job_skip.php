<?php
require_once(IA_ROOT_DIR."common/db/job.php");
require_once(IA_ROOT_DIR."www/controllers/job_filters.php");
require_once(IA_ROOT_DIR."common/db/task.php");

function controller_job_skip() {
    $filters = job_get_filters();

    if (!request_is_post()) {
        flash_error('Nu se pot ignora submisii.');
        redirect(url_monitor());
    }


    $job_ids = explode(",", request('skipped-jobs'));
    $jobs = array();
    foreach ($job_ids as $id) {
        $job = job_get_by_id((int)$id);
        if ($job == null) {
            continue;
        }

        identity_require('job-skip', $job);
        $jobs[] = $job;
    }

    $number = 0;
    foreach ($jobs as $job) {
        if ($job['status'] == 'skipped') {
            continue;
        }

        job_update($job['id'], 'skipped');
        ++$number;
    }

    flash('S-au ignorat ' . $number . ' job-uri');
    redirect(url_monitor($filters));
}
