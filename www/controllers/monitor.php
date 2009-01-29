<?php 

require_once(IA_ROOT_DIR."www/format/pager.php");
require_once(IA_ROOT_DIR."common/db/job.php");
require_once(IA_ROOT_DIR."common/db/task.php");
require_once(IA_ROOT_DIR."www/controllers/job_filters.php");

// Job monitor controller.
function controller_monitor() {
    global $identity_user;

    $view = array();
    $view['filters'] = job_get_filters();
    $view['user_name'] = getattr($identity_user, 'username');

    // First row.
    // FIXME: shouldn't this constant be in config.php? 
    $options = pager_init_options(array('display_entries' => 25));

    $job_data = job_get_range($view['filters'], $options['first_entry'], $options['display_entries']);
    $jobs = array();
    foreach ($job_data as $job) {
        if (!identity_can("job-view-score", $job)) {
            $job["score"] = NULL;
            if (identity_can("job-view-partial-feedback", $job)) {
                $task = task_get($job["task_id"]);
                if ($task["public_tests"]) {
                    $job["feedback_available"] = true;
                }
            }
        }
        if (!identity_can('job-view-source-size', $job)) {
            $job["job_size"] = NULL;
        }

        $jobs[] = $job;
    }

    $view['title'] = 'Monitorul de evaluare';
    $view['jobs'] = $jobs;
    $view['total_entries'] = job_get_count($view['filters']);
    $view['first_entry'] = $options['first_entry'];
    $view['display_entries'] = $options['display_entries'];
    $view['display_only_table'] = request('only_table', false);

    execute_view_die('views/monitor.php', $view);
}

?>
