<?php

require_once(Config::ROOT . "common/task.php");
require_once(Config::ROOT . "common/db/task.php");
require_once(Config::ROOT . "common/db/job.php");

function controller_job_detail($job_id) {
    $action = request('action', 'view');
    if ($action == 'view') {
        controller_job_view($job_id);
    } else if ($action == 'view-source') {
        controller_job_view_source($job_id);
    } else {
        FlashMessage::addError("Acțiune invalidă.");
        redirect(url_monitor());
    }
}

function controller_job_view($job_id) {
    // Get job id.
    if (!is_whole_number($job_id)) {
        FlashMessage::addError("Număr de job invalid.");
        redirect(url_monitor());
    }

    // Get job.
    $job = Job::get_by_id($job_id);
    if (!$job) {
        FlashMessage::addError("Nu există job-ul #$job_id.");
        redirect(url_monitor());
    }

    $round = $job->getRound();
    $task = $job->getTask();
    $user = $job->getUser();

    // Check security.
    Identity::enforceViewJob($job);
    $view = array();
    $view['title'] = 'Borderou de evaluare (job #'.$job_id.')';
    $view['job'] = $job;
    $view['round'] = $round;
    $view['task'] = $task;
    $view['user'] = $user;
    $view['tests'] = [];

    if ($job->isScoreViewable()) {
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

        if ($round && $round->type == 'classic') {
            $public_test_ids =
              task_parse_test_group($task->public_tests, $task->test_count);
            $public_test_ids = array_flip($public_test_ids);

            foreach ($view['tests'] as &$test) {
                $test['is_public_test'] =
                    array_key_exists($test['test_number'], $public_test_ids);
            }
        }

        /**
         * Get penalty amount and description
         */
        if ($round && $round->type == 'penalty-round') {
            $round_parameters = round_get_parameters($job->round_id);
            $description = '';
            $amount = 0;

            /**
             * Don't add time penalty if it is 0
             */
            $time_penalty = (int)((db_date_parse($job->submit_time) -
                    db_date_parse($round->start_time)) /
                    $round_parameters['decay_period']);

            if ($time_penalty > 0) {
                $description .= sprintf(
                    "%d (pentru %.1f minute)", $time_penalty,
                    ((db_date_parse($job->submit_time)) -
                     db_date_parse($round->start_time)) / 60);
                $amount += $time_penalty;
            }
            /**
             * Don't add submit penalty if it is 0
             */
            if ($job->submissions > 0) {
                if ($description != '') {
                    $description .= ' + ';
                }
                $description .= sprintf(
                    "%d (pentru %s)",
                    $job->submissions * $round_parameters['submit_cost'],
                    $job->submissions == 1 ? 'o submisie' :
                    ($job->submissions . ' submisii'));
                $amount += $job->submissions * $round_parameters['submit_cost'];
            }

            $view['penalty']['amount'] = $amount;
            $view['penalty']['description'] = $description;
        }
    } else {
        $view['group_tests'] = false;
        $view['job']->score = null;
        if ($job->isPartialFeedbackViewable()) {
            $view['tests'] =
                job_test_get_public(
                    $job_id,
                    $task->public_tests,
                    $task->test_count);
        }
    }

    if (!$view['job']->eval_message) {
        $view['job']->eval_message = "&nbsp";
    }
    execute_view_die('views/job_detail.php', $view);
}

function controller_job_view_source($job_id) {
    if (!is_whole_number($job_id)) {
        FlashMessage::addError("Număr de job invalid.");
        redirect(url_monitor());
    }

    // Get job.
    $job = Job::get_by_id($job_id);
    if (!$job) {
        FlashMessage::addError("Nu există job-ul #$job_id.");
        redirect(url_monitor());
    }

    Identity::enforceViewJobSource($job);
    if (!$job->isScoreViewable()) {
        $job->score = null;
    }

    $round = $job->getRound();
    $task = $job->getTask();
    $user = $job->getUser();

    $view = [];
    $view['title'] = 'Cod sursă (job #'.$job_id.')';
    $view['job'] = $job;
    $view['round'] = $round;
    $view['task'] = $task;
    $view['user'] = $user;
    $view['lang'] = $job->compiler_id;
    if ($view['lang'] == 'c') {
        $view['lang'] = 'cpp';
    }
    if ($view['lang'] == 'fpc') {
        $view['lang'] = 'delphi';
    }

    if ($task->open_source
        || $user->id == Identity::getId()
        || Identity::isAdmin()
        || (Request::isPost() && request('force_view_source'))
        || task_has_force_viewed_source($job->task_id, Identity::getId())
        || task_user_has_solved($job->task_id, Identity::getId())) {
        if (request('force_view_source')) {
            task_force_view_source($job->task_id, Identity::getId());
        }
        $view['first_view_source'] = false;
    } else {
        $view['first_view_source'] = true;
        unset($job->file_contents);
    }

    execute_view_die('views/job_view_source.php', $view);
}
