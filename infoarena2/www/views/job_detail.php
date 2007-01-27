<?php

require_once(IA_ROOT_DIR . 'www/views/header.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');
require_once(IA_ROOT_DIR . 'www/url.php');

?>


<h1><?= htmlentities($view['title']) ?></h1>

<table class="job">
<tr>
    <th class="task-id">Problema</th>
    <td class="task-id"><?= format_link(url_textblock($job['task_page_name']), $job['task_id']) ?></td>
    <th class="compiler-id">Compilator</th>
    <td class="compiler-id"><?= htmlentities($job['compiler_id']) ?></td>
</tr>
<tr>
    <th class="user-id">Utilizator</th>
    <td class="user-id"><?= format_user_tiny($job['user_name'], $job['user_fullname']) ?></td>
    <th class="submit-time">Data</th>
    <td class="submit-time"><?= format_date($job['submit_time']) ?></td>
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

<div class="job-eval-log">
<?php
$lines = explode("\n", $job['eval_log']); 
$eval_log = "";
$print_header = true;
$column_infos = array(
    array(
        'title' => 'Test', 
        'row' => 0,
        'col' => 2
    ),
    array(
        'title' => 'Timp de executie',
        'row' => 2,
        'col' => 1
    ),
    array(
        'title' => 'Memorie folosita',
        'row' => 3,
        'col' => 1
    ),
    array(
        'title' => 'Mesaj evaluare',
        'row' => 4
    ),
    array(
        'title' => 'Punctaj',
        'row' => 5,
        'col' => 0
    ),
);
  
foreach ($lines as $line) {
    $line_tokens = explode(": ", $line);

    if (strpos($line, 'Punctaj total') !== false) {
        $eval_log .= '<tr><td colspan="'.(count($column_infos)-1).'">'.
                     htmlentities($line_tokens[0]).'</td><td class = "total_score">'.
                     htmlentities($line_tokens[1]).'</td></tr>';
        continue;
    }
    if (strpos($line, 'Rulez testul') === false) {
        $eval_log .= $line . "\n";
        continue;
    }
    if ($print_header) {
        $eval_log .= '<table class="job_eval_log"><tr>';
        foreach ($column_infos as $column) {
            $eval_log .= "<th>".$column['title']."</th>";
        }
        $eval_log .= '</tr>';
        $print_header = false;
    }
  
    $eval_log .= '<tr>';
    foreach ($column_infos as $column) {
        $token = $line_tokens[$column['row']];
        if (isset($column['col'])) {
            $token = explode(" ", $token);
            $token = $token[$column['col']];
        }
        $eval_log .= '<td>' . htmlentities($token) . '</td>';
    }
    $eval_log .= "</tr>";
}
$eval_log .= "</table>";
echo $eval_log;
?>
<?= htmlentities($job['eval_message']) ?>
</div>
<?php } else { ?>
<p>Aceasta sursa nu a fost evaluata inca.</p>
<?php } ?>

<?php wiki_include('template/borderou'); ?>

<?php include('footer.php'); ?>
