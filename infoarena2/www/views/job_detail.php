<?php

include(IA_ROOT . 'www/views/header.php');
include(IA_ROOT . 'www/format/format.php');

?>

<h1><?= htmlentities($view['title']) ?></h1>

<div class="job-detail">
    <ul>
        <li class="task-id">
            <span class="desc">Problema:</span>
            <span class="val"><?= href(url($job['task_page_name']), $job['task_id']) ?></span>
        </li>
        <li class="user-id">
            <span class="desc">Utilizator:</strong></span>
            <span class="val"><?= format_user_tiny($job['user_name'], $job['user_fullname']) ?></span>
        </li>
        <li class="compiler-id">
            <span class="desc">Compilator:</span>
            <span class="val"><?= $job['compiler_id'] ?></span>
        </li>
        <li class="status">
            <span class="desc">Status:</span>
            <span class="val"><?= $job['status'] ?></span>
        </li>
        <li class="submit-time">
            <span class="desc">Data:</span>
            <span class="val"><?= htmlentities($job['submit_time']) ?></span>
        </li>
        <li class="eval-message">
            <span class="desc">Mesaj:</span>
            <span class="val"><?= $job['eval_message'] ?></span>
        </li>
        <li class="score">
            <span class="desc">Scor:</span>
            <span class="val"><?= $job['score'] ?></span>
        </li>
        <li class="eval-log">
            <span class="desc">Mesaje evaluare:</span>
            <span class="val"><?= htmlentities($job['eval_log']) ?></span>
        </li>
    </ul>
</div>
<?php include('footer.php'); ?>
