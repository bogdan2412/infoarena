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
    'showSourceLink' => true,
  ]);
  Smart::display('job/view.tpl');
}

function controller_job_view_source($job_id) {
  $job = loadJob($job_id);
  Identity::enforceViewJobSource($job);

  if (Request::isPost() && Request::has('force_view_source')) {
    task_force_view_source($job->task_id, Identity::getId());
    Util::redirect(url_job_view_source($job->id));
  }

  RecentPage::addCurrentPage("Cod sursă (job #{$job->id})");
  Smart::assign([
    'job' => $job,
    'showSourceLink' => false,
  ]);
  Smart::display('job/viewSource.tpl');
}

function loadJob(string $jobId): Job {
  $job = Job::get_by_id($jobId);
  if (!$job) {
    FlashMessage::addError('Nu există niciun job cu ID-ul cerut.');
    redirect(url_monitor());
  }

  return $job;
}
