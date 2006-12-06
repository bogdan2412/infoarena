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
                'width' => 512,
                'height' => 200,
            );

            // output gnuplot
            execute_view_die('views/plot_rating.php', $view);

        default:
            flash('Actiunea nu este valida.');
            redirect(url(''));
    }
}


?>
