<?php
require_once(Config::ROOT."common/db/job.php");
require_once(Config::ROOT."common/db/task.php");

function controller_reeval() {
  Identity::enforceReevalJobs();

  $referrer = $_SERVER['HTTP_REFERER'];
  if (!request_is_post()) {
    FlashMessage::addError('Nu pot ignora joburi printr-un request de tip GET.');
    redirect($referrer);
  }

  $filters = JobFilters::parseFromUrl($referrer);
  $jobCount = $filters->count();
  if ($jobCount > IA_REEVAL_MAXJOBS) {
    $msg = sprintf('Poți solicita reevaluarea a cel mult %s joburi.', IA_REEVAL_MAXJOBS);
    FlashMessage::addError($msg);
    redirect($referrer);
  }

  $jobs = $filters->getAll();
  foreach ($jobs as $job) {
    $job->status = 'waiting';
    $job->save();
  }

  // In theory we only need to trigger a full rating update when any of the
  // jobs belong to a completed, rated round. But this errs on the side of
  // caution.
  Variable::poke('Rating.fullUpdate', 1);

  FlashMessage::addSuccess('Am marcat joburile pentru reevaluare.');
  redirect($referrer);
}
?>
