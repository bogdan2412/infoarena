<?php

require_once(IA_ROOT . "common/db/task.php");

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
    static $last_task_id = null;
    static $task;
    static $params;

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
    if ($last_task_id != $task_id) {
        if (!is_task_id($task_id)) {
            return macro_error("Invalid task id");
        }
        $task = task_get($task_id);
        if ($task) {
            $params = task_get_parameters($task_id);
        }

        // remember
        $last_task_id = $task_id;
    }

    // validate task id
    if (!$task) {
        return macro_error("Invalid task identifier");
    }
    if (!identity_can('task-view', $task)) {
        return macro_permission_error();
    }

    // serve desired value
    switch ($param) {
        case 'title':
            return htmlentities($task['title']);

        case 'author':
            return htmlentities($task['author']);

        case 'source':
            return htmlentities($task['source']);

        case 'type':
            return htmlentities($task['type']);

        case 'id':
            return htmlentities($task['id']);

        default:
            if (!isset($params[$param])) {
                if (isset($args['default_value'])) {
                    return htmlentities($args['default_value']);
                } else {
                    return macro_error("Task doesn't have parameter '$param'");
                }
            } else {
                return htmlentities($params[$param]);
            }
    }
}
?>
