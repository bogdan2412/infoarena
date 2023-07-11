<?php
require_once(Config::ROOT."common/db/job.php");
require_once(Config::ROOT."common/db/task.php");

function controller_job_skip() {
  if (!request_is_post()) {
    FlashMessage::addError('Nu pot ignora joburi printr-un request de tip GET.');
    redirect(url_monitor());
  }

  $job_ids = explode(",", request('skipped-jobs'));
  $jobs = [];
  foreach ($job_ids as $id) {
    $job = job_get_by_id((int)$id);
    if ($job) {
      identity_require('job-skip', $job);
      $jobs[] = $job;
    }
  }

  $count = 0;
  foreach ($jobs as $job) {
    if ($job['status'] != 'skipped') {
      job_update($job['id'], 'skipped');
      $count++;
    }
  }

  FlashMessage::addSuccess('Am ignorat ' . $count . ' joburi.');
  $referer = $_SERVER['HTTP_REFERER'];
  redirect($referer);
}
