<?php

require_once(Config::ROOT . "common/db/task.php");
require_once(Config::ROOT . "common/db/user.php");
require_once(Config::ROOT . "common/db/tags.php");
require_once Config::ROOT.'common/db/task_statistics.php';
require_once(Config::ROOT . "www/format/format.php");
require_once(Config::ROOT . "www/macros/macro_stars.php");

// Displays a task field, be it a hard-coded field such as task author or a grader parameter such as `timelimit`.
// NOTE: The macro employs a simple caching mechanism (via static variables, cache expires at the end of the request)
//       to avoid multiple database queries.
//
// Arguments:
//      task_id (required)            Task identifier (without prefix)
//      param (required)              Parameter name. See the source code for possible values.
//      default_value (optional)      Display this when no such parameter is found
//
// Examples:
//      TaskParam(task_id="adunare" param="author")
//      TaskParam(task_id="adunare" param="timelimit")
function macro_taskparam($args) {
  $task_id = getattr($args, 'task_id');
  $param = getattr($args, 'param');

  // validate arguments
  if (!$task_id) {
    return macro_error("Expecting parameter `task_id`");
  }
  if (!$param) {
    return macro_error("Expecting parameter `param`");
  }

  // fetch task, parameters & textblock
  if (!is_task_id($task_id)) {
    return macro_error("Invalid task id");
  }

  $task = Task::get_by_id($task_id);

  // validate task id
  if (!$task) {
    return macro_error("Invalid task identifier");
  }
  if (!$task->isViewable()) {
    return macro_permission_error();
  }

  // serve desired value
  switch ($param) {
    case 'title':
      return html_escape($task->title);

    case 'author':
      $authors = task_get_authors($task->id);
      return implode(", ", format_task_author_tags($authors));

    case 'source':
      // TODO: This should also be converted into tags.
      return html_escape($task->source);

    case 'type':
      return html_escape($task->type);

    case 'id':
      return html_escape($task->id);

    case 'owner':
      if ($task->user_id == '0') {
        return '';
      }
      $user = user_get_by_id($task->user_id);
      return html_escape($user['full_name']);

    case 'formatted_owner':
      if ($task->user_id == '0') {
        return '';
      }
      $user = user_get_by_id($task->user_id);
      return format_user_tiny($user['username'], $user['full_name'],
                              $user['rating_cache']);

    case 'difficulty':
      if (is_null($task->rating)) {
        return 'N/A';
      }

      if ($task->security != 'public') {
        return 'N/A';
      }
      $star_args = array('rating' => $task->rating,
                         'scale' => 5,
                         'type' => 'normal');
      return macro_stars($star_args);

    case 'lastscore':
      if (Identity::isAnonymous() || !$task->isLastScoreViewable()) {
        return 'N/A';
      } else {
        $score = task_get_user_last_score($task->id, Identity::getId());
        if (is_null($score)) {
          return 'N/A';
        } else {
          return intval($score) . " puncte";
        }
      }
    case 'solved_by':
      if ($task->areStatsViewable()) {
        return (string)task_get_solved_by($task_id);
      } else {
        return 'N/A';
      }

    default:
      $params = task_get_parameters($task_id);
      if (!isset($params[$param])) {
        if (isset($args['default_value'])) {
          return html_escape($args['default_value']);
        } else {
          return macro_error("Task doesn't have parameter '$param'");
        }
      } else {
        return html_escape($params[$param]);
      }
  }
}
