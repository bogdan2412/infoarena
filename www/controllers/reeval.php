<?php
require_once(IA_ROOT_DIR."common/db/job.php");
require_once(IA_ROOT_DIR."www/controllers/job_filters.php");
require_once(IA_ROOT_DIR."common/db/task.php");

function controller_reeval() {
    $filters = job_get_filters();

    if (!isset($filters["task"]) ||
            !identity_can('task-reeval', task_get($filters['task']))) {
        identity_require('job-reeval');
    }

    if (!request_is_post()) {
        FlashMessage::addError('Nu se poate reevalua.');
        redirect(url_monitor());
    }
    $count = job_get_count($filters);
    if ($count > IA_REEVAL_MAXJOBS) {
        FlashMessage::addError('Prea multe job-uri!');
        redirect(url_monitor());
    }
    job_reeval($filters);

    // In theory we only need to trigger a full rating update when any of the
    // jobs belong to a completed, rated round. But this errs on the side of
    // caution.
    parameter_update_global('full_rating_update', 1);

    FlashMessage::addInfo('Reevaluez următoarele job-uri... ');
    redirect(url_monitor($filters));
}
?>
