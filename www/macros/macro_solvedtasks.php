<?php

require_once(IA_ROOT_DIR."common/db/user.php");

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
    $tasks = user_submitted_tasks($user['id'], $sel_solved, !$sel_solved);

    // view
    if (1 <= count($tasks)) {
        $urls = array();
        foreach ($tasks as $task) {
            $urls[] = format_link(url_textblock($task['page_name']), $task['id']);
        }
        if (1 == count($urls)) {
            $number = 'o problema';
        }
        else {
            $number = count($urls).' probleme';
        }
        return '<span class="task_enum">'.$number.': '.join(', ', $urls)
               .'</span>';
    }
    else {
        // no tasks
        return "<em>nici o problema</em>";
    }
}

?>
