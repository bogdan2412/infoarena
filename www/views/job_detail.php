<?php

require_once(IA_ROOT_DIR . 'www/views/header.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');
require_once(IA_ROOT_DIR . 'www/url.php');

// display group sum column?
$show_groups = ($view['group_count'] < count($view['tests']));

?>

<h1><?= htmlentities($view['title']) ?></h1>

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
<?php if (identity_can('job-view-source', $job)) { ?>
    <th class="source">Sursa</th>
    <td class="source"><?= format_link(url_job_view_source($job['id']), "Vezi sursa") ?></td>
<?php } ?>
</tr>

</table>

<h2>Raport evaluator</h2>

<?php if ('done' != $job['status']) { ?>
<p>Aceasta sursa nu a fost evaluata inca.</p>
<?php } else { ?>
    <div class="job-eval-log">
<?= htmlentities($job['eval_log']) ?>
    </div>
<?php } ?>

<?php if ('done' == $job['status'] && count($tests) > 0) { ?>
    <table class="job-eval-tests"> 
<thead>
    <tr>
        <th>Test</th>
        <th>Timp executie</th>
        <th>Memorie folosita</th>
        <th>Mesaj</th> 
        <th>Punctaj/test</th> 
        <?php if ($show_groups) { ?>
            <th>Punctaj/grupa</th> 
        <?php } ?>
    </tr>
</thead>
<tbody>
<?php
    $last_group = 0;
    foreach ($view['tests'] as $test) {
        echo '<tr class="'.($test['test_group'] % 2 == 1 ? "odd" : "even").'">';
        echo '<td class="number">'.$test['test_number'].'</td>';
        echo '<td class="number">'.$test['exec_time'].'ms</td>';
        echo '<td class="number">'.$test['mem_used'].'kb</td>';
        echo '<td>'.htmlentities($test['grader_message']).'</td>';
        echo '<td class="number">'.$test['points'].'</td>';
        if ($show_groups && $test['test_group'] != $last_group) {
            $last_group = $test['test_group'];
            echo '<td class="number" rowspan="'.$view['group_size'][$last_group].'">';
            echo $view['group_score'][$last_group];
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '<tr><td colspan="'.($show_groups ? 5 : 4).'">'.
         'Punctaj total</td><td class="total_score number">'.$job['score'].'</td></tr>';
?>
</tbody>
</table>
<?php } ?>
<?php wiki_include('template/borderou'); ?>

<?php include('footer.php'); ?>
