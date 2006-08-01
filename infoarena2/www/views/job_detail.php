<?php include('header.php'); ?>

<h1><?= htmlentities($view['title']) ?></h1>

<div class="jobDetail">
<ul>
    <li class="id">
        <span class="desc"><strong>ID</strong></span>
        <span class="val"><?= $job['id'] ?></span>
    </li>
    <li class="round_id">
        <span class="desc"><strong>ID runda</strong></span>
        <span class="val"><?= $job['round_id'] ?></span>
    </li>
    <li class="task_id">
        <span class="desc"><strong>ID task</strong></span>
        <span class="val"><?= $job['task_id'] ?></span>
    </li>
    <li class="user_id">
        <span class="desc"><strong>ID utilizator</strong></span>
        <span class="val"><?= $job['user_id'] ?></span>
    </li>
    <li class="file_extension">
        <span class="desc"><strong>extensie</strong></span>
        <span class="val"><?= $job['file_extension'] ?></span>
    </li>
    <li class="status">
        <span class="desc"><strong>status</strong></span>
        <span class="val"><?= $job['status'] ?></span>
    </li>
    <li class="timestamp">
        <span class="desc"><strong>timestamp</strong></span>
        <span class="val"><?= $job['timestamp'] ?></span>
    </li>
    <li class="score">
        <span class="desc"><strong>scor</strong></span>
        <span class="val"><?= $job['score'] ?></span>
    </li>
    <li class="eval_message">
        <span class="desc"><strong>mesaj eval</strong></span>
        <span class="val"><?= $job['eval_message'] ?></span>
    </li>
    <li class="mark_eval">
        <span class="desc"><strong>marcat</strong></span>
        <span class="val"><?= $job['mark_eval'] ?></span>
    </li>
</ul>

    <div class="evalLog">
        <span class="desc"><strong>Log evaluator</strong></span>
        <pre><?= $job['eval_log'] ?></pre>
    </div>
</div>
<?php include('footer.php'); ?>