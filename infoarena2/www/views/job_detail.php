<?php

require_once(IA_ROOT . 'www/views/header.php');
require_once(IA_ROOT . 'www/format/format.php');

?>

<h1><?= htmlentities($view['title']) ?></h1>

<table class="job">
<tr>
    <th class="task-id">Problema</th>
    <td class="task-id"><?= format_link(url_task($job['task_id']), $job['task_id']) ?></td>
    <th class="compiler-id">Compilator</th>
    <td class="compiler-id"><?= htmlentities($job['compiler_id']) ?></td>
</tr>
<tr>
    <th class="user-id">Utilizator</th>
    <td class="user-id"><?= format_user_tiny($job['user_name'], $job['user_fullname']) ?></td>
    <th class="submit-time">Data</th>
    <td class="submit-time"><?= htmlentities($job['submit_time']) ?></td>
</tr>
<tr>
    <th class="score">Scor</th>
    <td class="score"><?= htmlentities($job['score']) ?></td>
    <th class="status">Status</th>
    <td class="status"><strong><?= htmlentities($job['status']) ?></strong></td>
</tr>
<?php if (identity_can('job-download', $job)) { ?>
<tr>
    <th class="source">Sursa</th>
    <td colspan="3" class="source"><?= format_link(url_job_download($job['id']), "Vezi sursa trimisa") ?></td>
</tr>
<?php } ?>

</table>

<?php if ('done' == $job['status']) { ?>
<h2>Raport evaluator</h2>

<div class="job-eval-log"><?= htmlentities($job['eval_log']) ?>
<?= htmlentities($job['eval_message']) ?>
</div>
<?php } else { ?>
<p>Aceasta sursa nu a fost evaluata inca.</p>
<?php } ?>

<?php wiki_include('template/borderou'); ?>

<?php include('footer.php'); ?>
