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
  $anySkippableJobs = false;

  $options = pager_init_options([ 'display_entries' => MONITOR_ROWS ]);

  $job_data = job_get_range($filters, $options['first_entry'], $options['display_entries']);

  $jobs = [];
  foreach ($job_data as $job) {
    if (!identity_can('job-view-score', $job)) {
      $job['score'] = NULL;
      if (identity_can('job-view-partial-feedback', $job)) {
        $task = task_get($job['task_id']);
        if ($task['public_tests']) {
          $job['feedback_available'] = true;
        }
      }
    }
    if (!identity_can('job-view-source-size', $job)) {
      $job['job_size'] = NULL;
    }

    $job['can_skip'] = identity_can('job-skip', $job);
    $anySkippableJobs |= $job['can_skip'];

    $jobs[] = $job;
  }

  $jobCount = job_get_count($filters);
  $skipUrl = url_job_skip($filters);

  $pagerOptions = [
    'display_entries' => $options['display_entries'],
    'first_entry' => $options['first_entry'],
    'pager_style' => 'standard',
    'show_count' => true,
    'surround_pages' => 3,
    'total_entries' => job_get_count($filters),
    'url_args' => $filters + [ 'page' => 'monitor' ],
  ];

  RecentPage::addCurrentPage('Monitorul de evaluare');
  Smart::assign([
    'anySkippableJobs' => $anySkippableJobs,
    'filters' => $filters,
    'jobCount' => $jobCount,
    'jobs' => $jobs,
    'pagerOptions' => $pagerOptions,
    'showReevalForm' => showReevalForm($jobCount),
    'skipUrl' => $skipUrl,
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

// For the task column.
function format_task_link($row) {
  if (!identity_can('job-view', $row)){
    return '...';
  }
  return format_link(
    url_textblock($row['task_page_name']),
    $row['task_title']);
}

// For the round column.
function format_round_link($row) {
  if ($row['round_title']) {
    return format_link(
      url_textblock($row['round_page_name']),
      $row['round_title']);
  } else {
    return '';
  }
}

// For the size column.
function format_size($row) {
  if (is_null($row['job_size'])) {
    return '...';
  }
  $size = sprintf('%.2f', $row['job_size']/1024).' kb';
  if (identity_can('job-view-source', $row)) {
    return format_link(url_job_view_source($row['id']), $size);
  }
  return $size;
}

// For the date column.
function format_short_date($val) {
  return format_date($val, 'd MMM yyyy HH:mm:ss');
}

// For the score column.
function format_state($row) {
  $url = url_job_detail($row['id']);
  if ($row['status'] == 'done') {
    if (!is_null($row['score'])) {
      $msg = html_escape(sprintf('%s: %s puncte',
                                 $row['eval_message'], $row['score']));
    } else {
      $msg = html_escape($row['eval_message']);
      if (isset($row['feedback_available'])) {
        $msg .= ': rezultate parțiale disponibile';
      }
    }
    $msg = "<span class=\"job-status-done\">$msg</span>";
    return format_link($url, $msg, false);
  }
  if ($row['status'] == 'processing') {
    $msg = '<img src="'.url_static('images/indicator.gif').'">';
    $msg .= '<span class="job-status-processing">se evaluează';
    $msg .= '</span>';
    return format_link($url, $msg, false);
  }
  if ($row['status'] == 'waiting') {
    $msg = '<span class="job-stats-waiting">în așteptare</span>';
    return format_link($url, $msg, false);
  }

  if ($row['status'] == 'skipped') {
    $msg = '<span class="job-status-skipped">Job ignorat</span>';
    return format_link($url, $msg, false);
  }
  log_error('Invalid job status');
}

function format_skip($row) {
  if ($row['status'] == 'skipped') {
    return 'Ignorat';
  }

  if ($row['can_skip']) {
    $msg = format_tag(
      'input',
      null,
      array(
        'type' => 'checkbox',
        'class' => 'skip_job',
        'value' => $row['id']));
    $msg .= format_tag(
      'a',
      'ignoră',
      [
        'class' => 'skip-job-link',
        'href' => '#',
      ]);
    return $msg;
  }

  return '';
}
