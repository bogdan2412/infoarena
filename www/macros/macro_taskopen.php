<?php
require_once(IA_ROOT_DIR . "common/db/task.php");

function macro_taskopen($args) {
    $task_id = getattr($args, 'task_id');
    
    // validate arguments
    if (!$task_id) {
        return macro_error("Expecting parameter `task_id`");
    }

    // fetch task, parameters & textblock
    if (!is_task_id($task_id)) {
        return macro_error("Invalid task id");
    }

    $task = task_get($task_id);
    if (!$task['open_source'] && !$task['open_tests']) {
        return "";
    }
    $html = '<div class="banner">';
    $html .= '<img style="vertical-align: middle; float: left;" src="'.url_static("images/open_big.png").'">';
    if ($task['open_source']) {
        $html .= '<em>Pentru aceasta problema accesul la </em>'.
                 '<strong>'.format_link(url_monitor(array('task' => $task_id)), "toate sursele trimise").
                 '</strong><em> este liber!</em><br/>';
    }
    if ($task['open_tests']) {
        if ($task['open_source']) {
            $text = 'De asemenea, poti vedea si testele';
        } else {
            $text = 'Poti vedea testele pentru aceasta problema';
        }
        $html .= "<em>$text accesand </em><strong>".
                 format_link(url_attachment_list("problema/$task_id"), "atasamentele").
                 '</strong>.';
    }
    $html .= '</div>';
    return $html;
}
?>
