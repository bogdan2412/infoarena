<?php

require_once(IA_ROOT_DIR . "common/db/user.php");
require_once(IA_ROOT_DIR . "common/rating.php");

/**
 * Displays an image with user statistics.
 *
 * @param  string $username
 * @return
 */

function controller_userwidget($user_name) {
    // get data
    $dbuser = user_get_by_username($user_name);
    $task_data_succes = user_submitted_tasks($dbuser['id'], true, false);
    $task_data_failed = user_submitted_tasks($dbuser['id'], false, true);
    // get name
    $name = $dbuser['full_name'];
    // get rating
    $rating = $dbuser['rating_cache'];
    $rating = rating_scale($rating);
    $rating = sprintf("%01.0f", $rating);
    // get solved tasks
    $number_solved = sizeof($task_data_succes);
    // get failed tasks
    $number_failed = sizeof($task_data_failed);
    // calculate the succes
    if ($number_solved + $number_failed != 0) {
        $result = $number_solved / ($number_solved + $number_failed) * 100;
        $result = sprintf("%01.2f", $result);
        $succes = $result . "%";
    } else {
        $succes = "-";
    }
    $data=array(
        'name' => $name,
        'rating' => $rating,
        'task_data_succes' => $number_solved,
        'task_data_failed' => $number_failed,
        'succes' => $succes
    );
    execute_view_die('views/userwidget.php', $data);
}
?>
