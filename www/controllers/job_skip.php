<?php
require_once(Config::ROOT."common/db/job.php");
require_once(Config::ROOT."www/controllers/job_filters.php");
require_once(Config::ROOT."common/db/task.php");

function controller_job_skip() {
    $filters = job_get_filters();

    if (!request_is_post()) {
        FlashMessage::addError('Nu se pot ignora submisii.');
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

    FlashMessage::addSuccess('Am ignorat ' . $number . ' joburi.');
    redirect(url_monitor($filters));
}
