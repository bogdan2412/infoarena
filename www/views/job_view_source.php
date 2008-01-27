<?php
require_once(IA_ROOT_DIR . 'www/format/format.php');
require_once(IA_ROOT_DIR . 'www/url.php');
include('header.php');
?>

<h1>Cod sursa(job <?= format_link(url_job_detail($job['id']), '#'.$job['id']) ?>)</h1>

<table class="job">
<tr>
    <th class="user-id">Utilizator</th>
    <td class="user-id"><?= format_user_tiny($job['user_name'], $job['user_fullname']) ?></td>
    <th class="submit-time">Data</th>
    <td class="submit-time"><?= format_date($job['submit_time']) ?></td>
</tr>
<tr>
    <th class="task-id">Problema</th>
    <td class="task-id"><?= format_link(url_textblock($job['task_page_name']), $job['task_title']) ?></td>
    <th class="score">Scor</th>
    <td class="score"><?= htmlentities($job['score']) ?></td>
</tr>
</tr>
<tr>
    <th class="compiler-id">Compilator</th>
    <td class="compiler-id"><?= htmlentities($job['compiler_id']) ?></td>
    <th class="status">Status</th>
    <td class="status"><strong><?= htmlentities($job['status']) ?></strong></td>
</tr>
<tr>
    <th class="round-id">Runda</th>
    <td class="round-id" colspan="<?= identity_can('job-view-source', $job) ? 1 : 3 ?>">
    <?= format_link(url_textblock($job['round_page_name']), $job['round_title']) ?></td>

    <th class="size">Marime</th>
    <td class="size"><?= round($job['job_size']/1024, 2)." kb" ?></td>
</tr>
</table>
<div class="report">
<?php if ($topic_id) { ?>
<a href="<?= url_forum().'?action=post;topic='.$topic_id ?>">Raporteaza aceasta sursa</a>
<?php } else { ?>
<a href="<?= url_forum().'?action=pm;sa=send;u=3' ?>">Raporteaza aceasta sursa</a>
<?php } ?>
</div>
<?php
    echo '<div class="code">';
    echo "<textarea name=\"code\" class=\"{$lang}\" cols=\"60\" rows=\"10\">";
    echo htmlentities($job['file_contents']);
    echo '</textarea></div>';
    include('footer.php');
?>

