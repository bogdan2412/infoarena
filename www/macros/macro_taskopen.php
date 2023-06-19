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
    if (!$task['open_tests']) {
        return "";
    }
    $html = '<div class="banner">';
    $html .= '<img ' .
      'alt="open book" ' .
      'style="vertical-align: middle; float: left;" src="' .
      url_static('images/open_big.png') .
      '">';
    $text = 'Poți vedea testele pentru această problemă';
    $html .= "<em>$text accesând </em><strong>".
             format_link(
                url_attachment_list("problema/$task_id"), 'atașamentele').
             '</strong>.';
    $html .= '</div>';
    return $html;
}
?>
