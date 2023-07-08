<?php

require_once(Config::ROOT."common/db/user.php");
require_once Config::ROOT.'common/db/round.php';

// Display solved tasks for given user.
// When failed_tasks_hack is true, it displays failed tasks instead.
function macro_solvedtasks($args, $failed_tasks_hack = false) {
    $username = getattr($args, 'user');
    if (!$username) {
        return macro_error("Expecting argument `user`");
    }

    // validate user
    $user = user_get_by_username($username);
    if (!$user) {
        return macro_error("Nu such username: ".$username);
    }

    // get task list
    $sel_solved = !$failed_tasks_hack;

    $rounds = explode('|', getattr($args, 'rounds'));
    $tasks = user_submitted_tasks($user['id'], $rounds,
                                  $sel_solved, !$sel_solved);

    // view
    if (1 <= count($tasks)) {
        if (1 == count($tasks)) {
            $number = '<span class="task_enum">Total: o problemă</span><br>';
        }
        else {
            $number = '<span class="task_enum">Total: '.
                      count($tasks).' probleme</span><br>';
        }
        $all_problems = $number;
        foreach ($rounds as $round_id) {
            $current_line = round_get($round_id)['title'].': ';
            $urls = array();
            foreach ($tasks as $task) {
                if ($task['round_id'] == $round_id) {
                    $urls[] = format_link(url_textblock($task['page_name']),
                                          $task['id']);
                }
            }
            if (count($urls) == 0) {
                $current_line .= 'nicio problemă';
            } else if (count($urls) == 1) {
                $current_line .= 'o problemă<br>';
            } else {
                $current_line .= count($urls).' probleme <br>';
            }

            $current_line = '<span class="task_enum">'.$current_line.
                            implode(', ', $urls).'</span> <br>';
            if (count($urls) > 0) {
                $all_problems .= $current_line;
            }
        }

        return $all_problems;
    }
    else {
        // no tasks
        return '<em>Nicio problemă</em>';
    }
}

?>
