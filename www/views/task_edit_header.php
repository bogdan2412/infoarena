<?php

require_once(Config::ROOT . "www/format/format.php");
require_once(Config::ROOT . "www/format/list.php");
require_once(Config::ROOT . "www/url.php");

function task_edit_tabs($task_id, $active) {
  $tabs = [];
  $task = Task::get_by_id($task_id);

  if ($task->isEditable()) {
    $url = url_task_edit($task_id, 'edit');
    $tabs['edit'] = format_link($url, 'Enunț');
  }

  if ($task->isEditable()) {
    $url = url_task_edit($task_id, 'task-edit-params');
    $tabs['task-edit-params'] = format_link($url, 'Parametri');
  }

  if ($task->areTagsEditable()) {
    $url = url_task_edit($task_id, 'task-edit-tags');
    $tabs['task-edit-tags'] = format_link($url, 'Taguri');

  }
  if ($task->areRatingsEditable()) {
    $url = url_task_edit($task_id, 'task-edit-ratings');
    $tabs['task-edit-ratings'] = format_link($url, 'Ratinguri');
  }

  $tabs[$active] = [ $tabs[$active], [ 'class' => 'active' ] ];

  return format_ul($tabs, 'htabs');
}

?>
