<?php

require_once(IA_ROOT_DIR . "common/task.php");
require_once(IA_ROOT_DIR . "common/db/task.php");
require_once(IA_ROOT_DIR . "common/db/job.php");

function controller_job_detail($job_id) {
    $action = request('action', 'view');
    if ($action == 'view') {
        controller_job_view($job_id);
    } else if ($action == 'view-source') {
        controller_job_view_source($job_id);
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
    $view['tests'] = array();

    if (identity_can('job-view-score', $job)) {
        $view['group_tests'] = true;
        $view['group_count'] = 0;

        $view['tests'] = job_test_get_all($job_id);
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

        /**
         * Get penalty amount and description
         */
        if ($job['round_type'] == 'penalty-round') {
            $round_parameters = round_get_parameters($job['round_id']);
            $description = '';
            $amount = 0;

            /**
             * Don't add time penalty if it is 0
             */
            $time_penalty = (int)((db_date_parse($job['submit_time']) -
                    db_date_parse($job['round_start_time'])) /
                    $round_parameters['decay_period']);

            if ($time_penalty > 0) {
                $description .= sprintf(
                    "%d (pentru %.1f minute)", $time_penalty,
                    ((db_date_parse($job['submit_time'])) -
                     db_date_parse($job['round_start_time'])) / 60);
                $amount += $time_penalty;
            }
            /**
             * Don't add submit penalty if it is 0
             */
            if ($job['submissions'] > 0) {
                if ($description != '') {
                    $description .= ' + ';
                }
                $description .= sprintf(
                    "%d (pentru %s)",
                    $job['submissions'] * $round_parameters['submit_cost'],
                    $job['submissions'] == 1 ? 'o submisie' :
                    ($job['submissions'] . ' submisii'));
                $amount += $job['submissions'] * $round_parameters['submit_cost'];
            }

            $view['job']['penalty']['amount'] = $amount;
            $view['job']['penalty']['description'] = $description;
        }
    } else {
        $view['group_tests'] = false;
        $view['job']['score'] = NULL;
        if (identity_can("job-view-partial-feedback", $job)) {
            $view['tests'] = job_test_get_public($job_id);
        }
    }

    if (!$view['job']['eval_message']) {
        $view['job']['eval_message'] = "&nbsp";
    }
    execute_view_die('views/job_detail.php', $view);
}

function controller_job_view_source($job_id) {
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

    identity_require('job-view-source', $job);
    if (!identity_can("job-view-score", $job)) {
        $job['score'] = NULL;
    }

    $view = array();
    $view['title'] = 'Cod sursa (job #'.$job_id.')';
    $view['topic_id'] = task_get_topic($job['task_id']);
    $view['job'] = $job;
    $view['lang'] = $job['compiler_id'];
    if ($view['lang'] == 'c') {
        $view['lang'] = 'cpp';
    }
    if ($view['lang'] == 'fpc') {
        $view['lang'] = 'delphi';
    }
    execute_view_die('views/job_view_source.php', $view);
}

?>
