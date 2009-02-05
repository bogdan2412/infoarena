<?php

require_once(IA_ROOT_DIR."www/utilities.php");

function job_get_filters() {
    $available_filters = array('task', 'user', 'round', 'job_begin', 'job_end',
                               'job_id', 'time_begin', 'time_end', 'compiler',
                               'status', 'score_begin', 'score_end',
                               'eval_msg', 'task_hidden', 'remote_ip_info');

    $filters = array();
    foreach ($available_filters as $filter) {
        if (!is_null(request($filter)) && request($filter)) {
            if ($filter == "remote_ip_info" && !identity_can("job-view-ip", NULL)) {
                continue;
            }

            $filters[$filter] = request($filter);
        }
    }
    return $filters;
}
?>
