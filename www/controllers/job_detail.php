<?php

require_once(IA_ROOT_DIR . "common/db/task.php");
require_once(IA_ROOT_DIR . "common/db/job.php");

function controller_job_detail($job_id) {
    $action = request('action', 'view');
    if ($action == 'view') {
        controller_job_view($job_id);
    } else if ($action == 'download') {
        controller_job_download($job_id);
    } else {
        flash_error("Actiune invalida.");
        redirect(url_monitor());
    }
}

function controller_job_view($job_id) {
    // Get job id.
    if (!is_whole_number($job_id)) {
        flash_error("Numar de job invalid.");
        redirect(url_monitor());
    }

    // Get job.
    $job = job_get_by_id($job_id);
    if (!$job) {
        flash_error("Nu exista job-ul #$job_id");
        redirect(url_monitor());
    }

    // Check security.
    identity_require('job-view', $job);

    $view['title'] = 'Borderou de evaluare (job #'.$job_id.')';
    $view['job'] = $job;
    $view['tests'] = job_test_get_all($job_id);
    $view['group_count'] = 0;
    foreach ($view['tests'] as $test) {
        $view['group_count'] = max($view['group_count'], $test['test_group']);
    }
    for ($group = 1; $group <= $view['group_count']; $group++) {
        $view['group_score'][$group] = 0;
        $view['group_size'][$group] = 0;
        $solved_group = true;
        foreach ($view['tests'] as $test) {
            if ($test['test_group'] != $group) {
                continue;
            }
            $view['group_score'][$group] += $test['points'];
            $view['group_size'][$group]++;
            if (!$test['points']) {
                $solved_group = false;
            }
        }
        if (!$solved_group) {
            $view['group_score'][$group] = 0;
        }
    }

    if (!$view['job']['eval_message']) {
        $view['job']['eval_message'] = "&nbsp";
    }
    execute_view('views/job_detail.php', $view);
}

function controller_job_download($job_id) {
    if (!is_whole_number($job_id)) {
        flash_error("Numar de job invalid.");
        redirect(url_monitor());
    }

    // Get job.
    $job = job_get_by_id($job_id, true);
    if (!$job) {
        flash_error("Nu exista job-ul #$job_id");
        redirect(url_monitor());
    }

    identity_require('job-download', $job);

    header("Content-Type: text/plain");
    $filename = "{$job['task_id']}_{$job['user_name']}.{$job['compiler_id']}";
    header("Content-Disposition: inline; filename=".urlencode($filename).";");
    header('Content-Length: ' . strlen($job['file_contents']));
    echo $job['file_contents'];
}

?>
