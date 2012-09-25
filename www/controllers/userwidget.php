<?php

require_once(IA_ROOT_DIR . "common/db/user.php");
require_once(IA_ROOT_DIR . "www/format/format.php");

/**
 * Displays an image with user statistics.
 *
 * @param  string $username
 * @return
 */

function controller_userwidget($user_name) {
    // get data
    $db_user = user_get_by_username($user_name);
    $task_data_succes = user_submitted_tasks($db_user['id'], true, false);
    $task_data_failed = user_submitted_tasks($db_user['id'], false, true);
    // get name
    $name = $db_user['full_name'];
    // get rating
    $rating = $db_user['rating_cache'];
    $rating = rating_scale($rating);
    $rating = sprintf("%01.0f", $rating);
    // get solved tasks
    $number_solved = sizeof($task_data_succes);
    // get failed tasks
    $number_failed = sizeof($task_data_failed);
    // calculate the succes by formula:
    // Number of solved problems*100/(number solved+number failed)
    if ($number_solved + $number_failed != 0) {
        $result = $number_solved / ($number_solved + $number_failed) * 100;
        $result = sprintf("%01.2f", $result);
        $succes = $result . "%";
    } else {
        $succes = "-";
    }
    $is_admin = ($db_user['security_level'] === 'admin');
    // array containing the rating group and the color
    $rating_group = rating_group($rating, $is_admin);
    $hex = $rating_group['colour'];
    $red = hexdec(substr($hex, 1, 2));
    $green = hexdec(substr($hex, 3, 2));
    $blue = hexdec(substr($hex, 5, 2));
    $data = array(
        'name' => $name,
        'task_data_succes' => $number_solved,
        'task_data_failed' => $number_failed,
        'succes' => $succes,
        'rating' => $rating,
        'red' => $red,
        'green' => $green,
        'blue' => $blue
    );
    execute_view_die('views/userwidget.php', $data);
}

