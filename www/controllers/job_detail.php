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
        flash_error("Acțiune invalidă.");
        redirect(url_monitor());
    }
}

function controller_job_view($job_id) {
    // Get job id.
    if (!is_whole_number($job_id)) {
        flash_error("Număr de job invalid.");
        redirect(url_monitor());
    }

    // Get job.
    $job = job_get_by_id($job_id);
    if (!$job) {
        flash_error("Nu există job-ul #$job_id.");
        redirect(url_monitor());
    }

    // Check security.
    identity_require('job-view', $job);
    $view = array();
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

        if ($job['round_type'] == 'classic') {
            $public_test_ids =
                task_parse_test_group(
                    $job['task_public_tests'],
                    $job['task_test_count']);
            $public_test_ids = array_flip($public_test_ids);

            foreach ($view['tests'] as &$test) {
                $test['is_public_test'] =
                    array_key_exists($test['test_number'], $public_test_ids);
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
            $view['tests'] =
                job_test_get_public(
                    $job_id,
                    $job['task_public_tests'],
                    $job['task_test_count']);
        }
    }

    if (!$view['job']['eval_message']) {
        $view['job']['eval_message'] = "&nbsp";
    }
    execute_view_die('views/job_detail.php', $view);
}

function controller_job_view_source($job_id) {
    if (!is_whole_number($job_id)) {
        flash_error("Număr de job invalid.");
        redirect(url_monitor());
    }

    // Get job.
    $job = job_get_by_id($job_id, true);
    if (!$job) {
        flash_error("Nu există job-ul #$job_id.");
        redirect(url_monitor());
    }

    identity_require('job-view-source', $job);
    if (!identity_can("job-view-score", $job)) {
        $job['score'] = NULL;
    }

    $view = array();
    $view['title'] = 'Cod sursă (job #'.$job_id.')';
    $view['job'] = $job;
    $view['lang'] = $job['compiler_id'];
    if ($view['lang'] == 'c') {
        $view['lang'] = 'cpp';
    }
    if ($view['lang'] == 'fpc') {
        $view['lang'] = 'delphi';
    }

    if ($job['task_open_source']
        || $job['user_id'] == identity_get_user_id()
        || getattr(identity_get_user(), 'security_level') == 'admin'
        || (request_is_post() && request('force_view_source'))
        || task_has_force_viewed_source($job['task_id'], identity_get_user_id())
        || task_user_has_solved($job['task_id'], identity_get_user_id())) {
        if (request('force_view_source')) {
            task_force_view_source($job['task_id'], identity_get_user_id());
        }
        $view['first_view_source'] = false;
    } else {
        $view['first_view_source'] = true;
        unset($job['file_contents']);
    }

    execute_view_die('views/job_view_source.php', $view);
}
