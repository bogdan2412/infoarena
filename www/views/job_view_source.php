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
    <td class="score"><?= html_escape(is_null($job['score']) ? "Ascuns" : $job['score']) ?></td>
</tr>
</tr>
<tr>
    <th class="compiler-id">Compilator</th>
    <td class="compiler-id"><?= html_escape($job['compiler_id']) ?></td>
    <th class="status">Status</th>
    <td class="status"><strong><?= html_escape($job['status']) ?></strong></td>
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

if ($first_view_source) {
        $force_view_textblock = textblock_get_revision(IA_FORCE_VIEW_SOURCE_PAGE);
        echo '<div class="wiki_text_block">';
        echo wiki_process_textblock($force_view_textblock);
        echo '</div>';
?>
<form action="" method="POST">
    <center>
    <input type="submit" name="force_view_source" id="force_view_source" class="button important" value="Vezi sursa"/>
    </center>
</form>
<?php

    } else {
        echo '<div class="code">';
        echo '<pre><code>';
        echo html_escape($job['file_contents']);
        echo '</code></pre></div>';
    }
    include('footer.php');
?>

