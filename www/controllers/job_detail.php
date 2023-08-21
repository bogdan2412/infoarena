<?php

require_once __DIR__ . '/../../common/task.php';
require_once __DIR__ . '/../../common/db/task.php';
require_once __DIR__ . '/../../common/db/job.php';

function controller_job_detail($job_id) {
  $action = request('action', 'view');
  if ($action == 'view') {
    controller_job_view($job_id);
  } else if ($action == 'view-source') {
    controller_job_view_source($job_id);
  } else {
    FlashMessage::addError('Acțiune invalidă.');
    redirect(url_monitor());
  }
}

function controller_job_view(string $jobId): void {
  $job = loadJob($jobId);
  Identity::enforceViewJob($job);

  $round = $job->getRound();
  $task = $job->getTask();
  $penalty = $job->getPenalty();
  $tests = new JobTaskTests($job, $task);

  $showScoreTable =
    $job->isDone() &&
    $tests->hasJobTests() &&
    ($job->isScoreViewable() || $job->isPartialFeedbackViewable());

  $showGroups =
    $job->isScoreViewable() &&
    $tests->hasGroups();

  $showFeedbackColumn =
    $round &&
    ($round->type == 'classic') &&
    $tests->hasPublicTests();

  $numColumns = 5 + $showGroups + $showFeedbackColumn;

  RecentPage::addCurrentPage("Borderou de evaluare (job #{$job->id})");
  Smart::assign([
    'job' => $job,
    'numColumns' => $numColumns,
    'penalty' => $penalty,
    'tests' => $tests,
    'showFeedbackColumn' => $showFeedbackColumn,
    'showGroups' => $showGroups,
    'showScoreTable' => $showScoreTable,
  ]);
  Smart::display('job/view.tpl');
}

function controller_job_view_source($job_id) {
  $job = loadJob($job_id);
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

function loadJob(string $jobId): Job {
  $job = Job::get_by_id($jobId);
  if (!$job) {
    FlashMessage::addError('Nu există niciun job cu ID-ul cerut.');
    redirect(url_monitor());
  }

  return $job;
}
