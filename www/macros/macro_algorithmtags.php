<?php

// ==AlgorithmTags(task_id="task_id")==
function macro_algorithmtags($args): string {
  $taskId = $args['task_id'] ?? null;
  $task = Task::get_by_id($taskId);
  if (!$task || !$task->areTagsViewable()) {
    return '';
  }

  $tagTree = $task->getMethodsAndAlgorithms();
  if (!count($tagTree)) {
    return '';
  }

  Smart::assign([
    'tagTree' => $tagTree,
  ]);
  return Smart::fetch('macro/algorithmTags.tpl');
}
