<?php

  require_once(IA_ROOT_DIR . 'www/views/header.php');
  require_once(IA_ROOT_DIR . 'www/format/format.php');
  require_once(IA_ROOT_DIR . 'www/url.php');

  // display group sum column?
  $show_groups = $view['group_tests'] &&
                 ($view['group_count'] < count($view['tests']));

?>

<h1><?= html_escape($view['title']) ?></h1>

<table class="job">
  <tr>
    <th class="user-id">Utilizator</th>
    <td class="user-id"><?= format_user_tiny($job['user_name'], $job['user_fullname']) ?></td>
    <th class="submit-time">Dată</th>
    <td class="submit-time"><?= format_date($job['submit_time']) ?></td>
  </tr>
  <tr>
    <th class="task-id">Problemă</th>
    <td class="task-id"><?= format_link(url_textblock($job['task_page_name']), $job['task_title']) ?></td>
    <th class="status">Status</th>
    <td class="status"><strong><?= html_escape($job['status']) ?></strong></td>
  </tr>
  <tr>
    <th class="round-id">Rundă</th>
    <td class="round-id">
      <?= format_link(url_textblock($job['round_page_name']), $job['round_title']) ?></td>
    <th class="compiler-id">Compilator</th>
    <td class="compiler-id">
      <?= html_escape($job['compiler_id']) ?>
      <?php if (identity_can('job-view-source', $job)) { ?>
        | <?= format_link(url_job_view_source($job['id']), "Vezi sursa") ?>
      <?php } ?>
    </td>
  </tr>
  <tr>
    <th class="score">Scor</th>
    <td class="score" colspan="<?= identity_can('job-view-ip', $job) ? 1 : 3 ?>">
      <?= html_escape(is_null($job['score']) ? "Ascuns" : $job['score']) ?></td>
    <?php if (identity_can('job-view-ip', $job)) { ?>
      <th class="ip">IP</th>
      <td class="ip"><?= $job['remote_ip_info'] ? html_escape($job['remote_ip_info']) : '<em>lipseste</em>' ?></td>
    <?php } ?>
  </tr>

</table>

<h2>Raport evaluator</h2>

<?php if ('done' != $job['status']) { ?>
  <p>Această sursă nu a fost evaluată încă.</p>
<?php } else { ?>
  <div class="job-eval-log">
    <?= html_escape($job['eval_log']) ?>
  </div>
<?php } ?>

<?php

  if ('done' == $job['status'] && count($tests) > 0) {
    $show_feedback_column = false;
    foreach ($view['tests'] as $test) {
      if (getattr($test, 'is_public_test')) {
        $show_feedback_column = true;
      }
    }
?>
<table class="job-eval-tests alternating-colors">
  <thead>
    <tr>
      <?php

        if ($show_feedback_column) {
          $url = url_static('images/visible.png');
          echo '<th><img src="' . $url . '" '.
               'title="Feedback" alt="Feedback" '.
               'style="height: 18px; width: auto;"></th>';
        } ?>
      <th>Test</th>
      <th>Timp execuție</th>
      <th>Memorie folosită</th>
      <th>Mesaj</th>
      <th>Punctaj/test</th>
      <?php if ($show_groups) { ?>
        <th>Punctaj/grupă</th>
      <?php } ?>
    </tr>
  </thead>
  <tbody>
    <?php
      $last_group = 0;
      foreach ($view['tests'] as $test) {
        $row_css_class = get_row_css_class($view['group_tests'], $test['test_group']);
        echo "<tr class=\"{$row_css_class}\">";
        if ($show_feedback_column) {
          if (!getattr($test, 'is_public_test')) {
            echo '<td class="number">✗</td>';
          } else {
            echo '<td class="number">✓</td>';
          }
        }

        echo '<td class="number">'.$test['test_number'].'</td>';
        if ($test["grader_message"] == "Time limit exceeded.") {
          echo '<td class="number">Depășit</td>';
        } else {
          echo '<td class="number">'.$test['exec_time'].' ms</td>';
        }
        if ($test["grader_message"] == "Memory limit exceeded.") {
          echo '<td class="number">Depășit</td>';
        } else {
          echo '<td class="number">'.$test['mem_used'].' kb</td>';
        }
        echo '<td>'.html_escape($test['grader_message']).'</td>';
        echo '<td class="number">'.$test['points'].'</td>';
        if ($show_groups && $test['test_group'] != $last_group) {
          $last_group = $test['test_group'];
          echo '<td class="number" rowspan="'.$view['group_size'][$last_group].'">';
          echo $view['group_score'][$last_group];
          echo '</td>';
        }
        echo '</tr>';
      }
      $line_spanning = 4 + ($show_groups ? 1: 0) + ($show_feedback_column ? 1: 0);
      if (getattr($job, 'penalty') !== null) {
        echo '<tr><td colspan="'.$line_spanning.'"> Penalizare '
           . $job['penalty']['description'] . '</td><td class="number">-'
           . $job['penalty']['amount'] . '</td></tr>';
      }

      if (!is_null($job['score'])) {
        echo '<tr><td colspan="'.$line_spanning.'">'.
             'Punctaj total</td><td class="total_score number">'.$job['score'].'</td></tr>';
      }
    ?>
  </tbody>
</table>
<?php } ?>
<?php Wiki::include('template/borderou'); ?>

<?php

include('footer.php');

function get_row_css_class($jobHasTestGroups, $testGroup): string {
  return $jobHasTestGroups
    ? (($testGroup % 2) ? 'color0' : 'color1')
    : '';
}

?>
