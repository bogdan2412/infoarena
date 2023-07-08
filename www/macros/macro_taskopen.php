<?php
require_once(Config::ROOT . "common/db/task.php");

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
    $imgSrc = url_static('images/open_big.png');
    $link = format_link(url_attachment_list("problema/$task_id"), 'atașamentele');
    $html = sprintf(
      '<div class="banner">' .
      '<div>' .
      '<img alt="open book" src="%s">' .
      'Poți vedea testele pentru această problemă accesând %s.' .
      '</div>' .
      '</div>',
      $imgSrc, $link);
    return $html;
}
?>
