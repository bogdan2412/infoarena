<?php

require_once(Config::ROOT . 'common/db/user.php');
require_once(Config::ROOT . 'common/db/score.php');
require_once(Config::ROOT . 'common/user.php');
require_once(Config::ROOT . 'common/email.php');

function controller_penalty_edit() {
    //security check
    if (!Identity::isAdmin()) {
        Util::redirectToHome();
    }

    //get parameters
    $user_id = request('user_id');
    $round_id = request('round_id');

    if (!$user_id || !$round_id) {
        redirect(url_penalty());
    }

    //needed info
    $scores = scores_get_by_user_id_and_round_id($user_id, $round_id);
    $total_score = total_score_get_by_user_id_and_round_id($user_id, $round_id);

    //submit?!
    $submit = Request::isPost();

    if ($submit) {
        foreach ($scores as $task) {
            $task['score'] = getattr($_POST, $task['task_id']);
            echo getattr($_POST, $task['task_id']);
            score_update($task['user_id'], $task['task_id'], $task['round_id'], $task['score']);
        }

        Util::redirectToHome();
    } else {
        //initial display of the form
    }

    //page data
    $view = array();
    $view['title'] = 'Penalty Edit';
    $view['total_score'] = $total_score['score'];
    $view['tasks'] = $scores;
    $view['user'] = user_get_by_id($user_id);
    $view['round'] = round_get($round_id);
    execute_view_die('views/penalty_edit.php', $view);
}
