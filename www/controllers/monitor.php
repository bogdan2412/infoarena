<?php

require_once Config::ROOT . 'common/db/job.php';
require_once Config::ROOT . 'common/db/task.php';
require_once Config::ROOT . 'www/controllers/job_filters.php';
require_once Config::ROOT . 'www/format/format.php';
require_once Config::ROOT . 'www/format/list.php';
require_once Config::ROOT . 'www/format/pager.php';

const MONITOR_ROWS = 25;

// Job monitor controller.
function controller_monitor() {
  $filters = job_get_filters();

  $options = pager_init_options([ 'display_entries' => MONITOR_ROWS ]);

  $jobs = Job::getWithFilters($filters, $options['first_entry'], $options['display_entries']);
  $jobCount = Job::countWithFilters($filters);

  $pagerOptions = [
    'display_entries' => $options['display_entries'],
    'first_entry' => $options['first_entry'],
    'pager_style' => 'standard',
    'show_count' => true,
    'surround_pages' => 3,
    'total_entries' => $jobCount,
    'url_args' => $filters + [ 'page' => 'monitor' ],
  ];

  RecentPage::addCurrentPage('Monitorul de evaluare');
  Smart::assign([
    'filters' => $filters,
    'jobCount' => $jobCount,
    'jobs' => $jobs,
    'pagerOptions' => $pagerOptions,
    'showReevalForm' => showReevalForm($jobCount),
    'showSkips' => User::isAdmin(),
    'tabs' => makeTabs($filters),
  ]);
  Smart::addResources('monitor');
  Smart::display('monitor.tpl');
}

function showReevalForm(int $jobCount): bool {
  return
    ($jobCount <= IA_REEVAL_MAXJOBS) &&
    (identity_can('job-reeval') ||
     (request('task') && identity_can('task-reeval', task_get(request('task')))));
}

function makeTabs(array $filters): array {
  $tabs = [];
  $selected = null;
  $me = User::getCurrentUsername();

  // my jobs tab
  $user = getattr($filters, 'user');
  if ($me) {
    $tabFilters = [ 'user' => $me ];
    $tabs['mine'] = format_link(url_monitor($tabFilters), 'Soluțiile mele');
    if ($me == $user) {
      $selected = 'mine';
    }
  }

  // all jobs tab
  $tabs['all'] = format_link(url_monitor(), 'Toate soluțiile');
  if (is_null($selected)) {
    $selected = 'all';
  }

  // custom user tab
  if ($user && ($user != $me)) {
    $tabFilters = [ 'user' => $user ];
    $tabs['custom'] = format_link(url_monitor($tabFilters), "Trimise de {$user}");
    $selected = 'custom';
  }

  $tabs[$selected] = [ $tabs[$selected], ['class' => 'active'] ];
  return $tabs;
}
