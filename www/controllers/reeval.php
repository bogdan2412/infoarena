<?php
require_once(Config::ROOT."common/db/job.php");
require_once(Config::ROOT."common/db/task.php");

function controller_reeval() {
  if (!User::canReevalJobs()) {
    FlashMessage::addError('Nu ai permisiunea să reevaluezi joburi.');
    redirect(url_home());
  }

  $referer = $_SERVER['HTTP_REFERER'];
  if (!request_is_post()) {
    FlashMessage::addError('Nu pot ignora joburi printr-un request de tip GET.');
    redirect($referer);
  }

  $filters = JobFilters::parseFromUrl($referer);
  $jobCount = Job::countWithFilters($filters);
  if ($jobCount > IA_REEVAL_MAXJOBS) {
    $msg = sprintf('Poți solicita reevaluarea a cel mult %s joburi.', IA_REEVAL_MAXJOBS);
    FlashMessage::addError($msg);
    redirect($referer);
  }

  $jobs = Job::getAllWithFilters($filters);
  foreach ($jobs as $job) {
    $job->status = 'waiting';
    $job->save();
  }

  // In theory we only need to trigger a full rating update when any of the
  // jobs belong to a completed, rated round. But this errs on the side of
  // caution.
  parameter_update_global('full_rating_update', 1);

  FlashMessage::addSuccess('Am marcat joburile pentru reevaluare.');
  redirect($referer);
}
?>
