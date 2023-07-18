<?php
require_once(Config::ROOT . 'www/format/format.php');
require_once(Config::ROOT . 'www/url.php');
require_once 'header.php';
?>

<h1>Cod sursă (job <?= format_link(url_job_detail($job->id), '#'.$job->id) ?>)</h1>

<table class="job">
  <tr>
    <th class="user-id">Utilizator</th>
    <td class="user-id"><?= format_user_tiny($user->username, $user->full_name) ?></td>
    <th class="submit-time">Dată</th>
    <td class="submit-time"><?= format_date($job->submit_time) ?></td>
  </tr>
  <tr>
    <th class="task-id">Problemă</th>
    <td class="task-id"><?= format_link(url_textblock($task->page_name), $task->title) ?></td>
    <th class="score">Scor</th>
    <td class="score"><?= html_escape(is_null($job->score) ? 'Ascuns' : $job->score) ?></td>
  </tr>
  <tr>
    <th class="compiler-id">Compilator</th>
    <td class="compiler-id"><?= html_escape($job->compiler_id) ?></td>
    <th class="status">Status</th>
    <td class="status"><strong><?= html_escape($job->status) ?></strong></td>
  </tr>
  <tr>
    <th class="round-id">Rundă</th>
    <td class="round-id" colspan="<?= $job->isSourceViewable() ? 1 : 3 ?>">
      <?= format_link(url_textblock($round->page_name), $round->title) ?></td>

    <th class="size">Mărime</th>
    <td class="size"><?= $job->getSizeString() ?></td>
  </tr>
</table>
<?php

  if ($first_view_source) {
    $force_view_textblock = textblock_get_revision(IA_FORCE_VIEW_SOURCE_PAGE);
    echo '<div class="wiki_text_block">';
    echo Wiki::processTextblock($force_view_textblock);
    echo '</div>';
?>
<form action="" method="POST">
  <input type="submit" name="force_view_source" id="force_view_source" class="button important" value="Vezi sursa">
</form>
<?php

    } else {
        echo '<div class="code">';
        echo '<pre><code>';
        echo html_escape($job->file_contents);
        echo '</code></pre></div>';
    }
    include('footer.php');
?>
