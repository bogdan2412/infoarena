<?php
require_once(IA_ROOT_DIR."common/db/job.php");
require_once(IA_ROOT_DIR."www/controllers/job_filters.php");
require_once(IA_ROOT_DIR."common/db/task.php");

function controller_reeval() {
    $filters = job_get_filters();

    if (!$filters["task"] ||
            !identity_can('task-reeval', task_get($filters['task']))) {
        identity_require('job-reeval');
    }

    if (!request_is_post()) {
        flash_error('Nu se poate reevalua.');
        redirect(url_monitor());
    }
    $count = job_get_count($filters);
    if ($count > IA_REEVAL_MAXJOBS) {
        flash_error('Prea multe job-uri!');
        redirect(url_monitor());
    }
    job_reeval($filters);
    flash('Se reevalueaza urmatoarele job-uri... ');
    redirect(url_monitor($filters));
}
?>
