<?php

require_once Config::ROOT . 'www/macros/macro_stars.php';

function macro_taskheader($args) {
  $taskId = $args['task_id'] ?? '';
  $task = Task::get_by_id($taskId);
  if (!$task) {
    return macro_error("Problema „{$taskId}” nu există.");
  }

  $owner = User::get_by_id($task->user_id);
  $authors = $task->getTags('author');

  Smart::assign([
    'authors' => $authors,
    'owner' => $owner,
    'score' => $task->getIdentityMaxScore(),
    'task' => $task,
  ]);
  return Smart::fetch('macro/taskHeader.tpl');
}
