<?php

require_once(IA_ROOT."common/db/user.php");
require_once(IA_ROOT."common/db/score.php");

// This controller serves real time plots (graphs) rendered
// with gnuplot.
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
            log_print("Plotting rating history of user ".$username);

            // get rating history
            $history = rating_history($user['id']);

            // view
            $view = array(
                'history' => $history,
                'user' => $user,
            );

            // output gnuplot
            execute_view_die('views/plot_rating.php', $view);

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

            if ($user) {
                log_print("Plotting rating distribution for ".$username);
            }
            else {
                log_print("Plotting global rating distribution");
            }

            // get rating history
            //
            // Note: This bucket size is relative to the absolute ratings
            // ranging from ~1000 to ~2500
            $bucket_size = 20;
            $distribution = rating_distribution($bucket_size);

            // view
            $view = array(
                'distribution' => $distribution,
                'bucket_size' => $bucket_size,
                'user' => $user,
            );

            // output gnuplot
            execute_view_die('views/plot_distribution.php', $view);
        default:
            flash('Actiunea nu este valida.');
            redirect(url_home());
    }
}


?>
