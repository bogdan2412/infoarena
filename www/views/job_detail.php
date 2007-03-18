<?php

require_once(IA_ROOT_DIR . 'www/views/header.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');
require_once(IA_ROOT_DIR . 'www/url.php');

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
    <td class="round-id" colspan="3"><?= format_link(url_textblock($job['round_page_name']), $job['round_title']) ?></td>
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

$eval_log = "";
$before_table = true;
$after_table = false;

// Parse every line of eval_log
// We have some creepy logic to print a table in the middle.
// FIXME: This is some of the worst code ever.
$lines = explode("\n", $job['eval_log']); 
foreach ($lines as $line) {
    if ($after_table) {
        $eval_log .= $line . '<br />';
        continue;
    }

    // Parse total score line.
    if (preg_match('/^Punctaj total: ([0-9]+)$/', $line, $matches)) {
        $eval_log .= '<tr>';
        $eval_log .= '<td colspan="4">Punctaj total:</td>';
        $eval_log .= '<td class="total_score">';
        $eval_log .= $matches[1];
        $eval_log .= '</td></tr></table>';
        $after_table = true;
        continue;
    }

    // Parse test-run line.
    if (preg_match('/^Rulez testul ([0-9]+): '.
            '([^:]*): timp ([0-9]+)ms: mem ([0-9]+)kb: '.
            '(.*): ([0-9]+) puncte$/', $line, $matches)) {
        $test_no = $matches[1];
        $test_status = $matches[2];
        $test_time = $matches[3];
        $test_mem = $matches[4];
        $test_msg = $matches[5];
        $test_score = $matches[6];

        // Print the header when we see the first row.
        if ($before_table) {
            $eval_log .= '<table class="job_eval_log"><tr>';
            $eval_log .= "<th>Test</th>";
            $eval_log .= "<th>Timp executie</th>";
            $eval_log .= "<th>Memorie folosita</th>";
            $eval_log .= "<th>Mesaj</th>";
            $eval_log .= "<th>Punctaj</th>";
            $eval_log .= "</tr>";
            $before_table = false;
        }

        // Print actual data.
        $eval_log .= '<tr>';
        $eval_log .= '<td>'.$test_no.'</td>';
        $eval_log .= '<td>'.$test_time.'ms</td>';
        $eval_log .= '<td>'.$test_mem.'kb</td>';
        $eval_log .= '<td>'.$test_msg.'</td>';
        $eval_log .= '<td>'.$test_score.'</td>';
        $eval_log .= "</tr>";

        continue;
    }

    // Pass everything else straight through.
    if ($before_table) {
        $eval_log .= $line . '<br />';
    } else {
        // Only blank lines between table and final score.
    }
}

if (!$after_table) {
    $eval_log .= "</table>";
}
echo $eval_log;

?>
<?= htmlentities($job['eval_message']) ?>
</div>
<?php } else { ?>
<p>Aceasta sursa nu a fost evaluata inca.</p>
<?php } ?>

<?php wiki_include('template/borderou'); ?>

<?php include('footer.php'); ?>
