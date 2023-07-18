<?php

require_once Config::ROOT.'common/db/task_statistics.php';
require_once Config::ROOT.'common/db/task.php';
require_once Config::ROOT.'common/statistics-config.php';

function controller_statistici_problema() {
  // Validate task_id
  $task_id = request('task');
  if (!is_task_id($task_id)) {
    FlashMessage::addError('Identificatorul de task este invalid.');
    redirect(url_home());
  }

  // Get task
  $task = Task::get_by_id($task_id);
  if (!$task) {
    FlashMessage::addError('Problema nu există.');
    redirect(url_home());
  }

  // Security check
  Identity::enforceViewTaskStats($task);

  $round_id = request('round');
  if ($round_id === null) {
    $round_id = task_get_archive_round($task_id);
  }

  $round = round_get($round_id);
  if (!$round) {
    FlashMessage::addError('Runda nu există');
    redirect(url_home());
  }

  $view = array();
  $view['title'] = 'Statisticile problemei ' . $task->title;
  $view['task_id'] = $task_id;
  $view['task_url'] = $task->page_name;
  $view['round_id'] = $round['id'];
  $view['round_name'] = $round['title'];

  $best_by_time = task_statistics_get_top_users($task_id,
                                                'time',
                                                $round_id,
                                                IA_STATISTICS_MAX_TOP_SIZE);
  $best_by_memory = task_statistics_get_top_users($task_id,
                                                  'memory',
                                                  $round_id,
                                                  IA_STATISTICS_MAX_TOP_SIZE);
  $best_by_size = task_statistics_get_top_users($task_id,
                                                'size',
                                                $round_id,
                                                IA_STATISTICS_MAX_TOP_SIZE);
  $data = array(
    'time' => $best_by_time,
    'memory' => $best_by_memory,
    'size' => $best_by_size,
  );
  $measurement_unit = array(
    'time' => 'ms',
    'memory' => 'kb',
    'size' => 'b',
  );

  foreach ($data as $criteria => &$ranking) {
    $position = 1;
    foreach ($ranking as &$contestant) {
      $contestant['position'] = $position;
      $contestant['special_score'] .= ' ' . $measurement_unit[$criteria];
      $position = $position + 1;
    }
  }

  $view['data'] = $data;

  if (Identity::isLoggedIn()) {
    $user_id = Identity::getId();
    $view['user_wrong_submissions'] =
      task_statistics_get_user_wrong_submissions($task_id,
                                                 $user_id,
                                                 $round_id);
    $view['username'] = Identity::getUsername();
  }

  $view['average_wrong_submissions'] =
    task_statistics_get_average_wrong_submissions($task_id, $round_id);
  $view['solved_percentage'] =
    task_statistics_get_solved_percentage($task_id, $round_id);

  execute_view_die('views/statistici_problema.php', $view);
}
