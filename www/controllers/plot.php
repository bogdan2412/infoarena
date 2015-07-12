<?php

require_once IA_ROOT_DIR.'common/db/user.php';
require_once IA_ROOT_DIR.'common/db/score.php';
require_once IA_ROOT_DIR.'common/db/task_statistics.php';

// This controller serves real time data for plots (graphs) rendered
// with Open Flash Chart.
function controller_plot($suburl) {
    switch ($suburl) {
        case 'rating':
            // Display user rating history
            $username = request('user');

            // validate user
            $user = user_get_by_username($username);
            if (!$user) {
                die_http_error();
            }

            // get rating history
            $history = rating_history($user['id']);

            // view
            $view = array(
                'history' => $history,
                'user' => $user,
            );

            // output data for Open Flash Chart
            execute_view_die('views/plot_rating.php', $view);
            break;

        case 'distribution':
            // Display rating distribution
            // If there is a username specified, plot given user in rating
            // distribution.

            // validate user
            $username = request('user');
            $user = user_get_by_username($username);

            if (!$user && $username) {
                die_http_error();
            }

            // get rating history
            //
            // Note: This bucket size is relative to the absolute ratings
            // ranging from ~1000 to ~2500
            $bucket_size = 60;
            $distribution = rating_distribution($bucket_size);

            // view
            $view = array(
                'distribution' => $distribution,
                'bucket_size' => $bucket_size,
                'user' => $user,
            );

            // output data for Open Flash Chart
            execute_view_die('views/plot_distribution.php', $view);
            break;

        case 'points_distribution':
            // Display points distribution
            // If there is a username specified, plot given user in points
            // distribution.

            $args = request('args');
            $args = explode(',', $args);
            $username = $args[0];
            $user = user_get_by_username($username);
            $task_id = $args[1];

            // validate user
            if ((!$user && $username) || !$task_id) {
                die_http_error();
            }

            $points_distribution = task_statistics_get_points_distribution(
                                                                    $task_id,
                                                                    'arhiva');

            // view
            $view = array(
                'points_distribution' => $points_distribution,
            );

            if ($user) {
                $user_points = task_get_user_score($task_id, $user['id']);
                $view['user_points'] = $user_points;
            }

            // output data for Open Flash Chart
            execute_view_die('views/plot_points_distribution.php', $view);
            break;

        default:
            flash('Actiunea nu este valida.');
            redirect(url_home());
            break;
    }
}
