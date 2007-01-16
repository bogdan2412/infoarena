<?php

require_once(IA_ROOT . 'www/format/table.php');
require_once(IA_ROOT . 'www/format/format.php');

include('header.php');

echo '<h1>'.htmlentities($view['title']).'</h1>';

if (!$jobs) {
    print "<h3>Nici o solutie in coada de evaluare</h3>";
} else {
        // For the score column.
        function format_state($row) {
            $url = url_job_detail($row['id']);
            if ($row['status'] == 'done') {
                $msg = htmlentities(sprintf("%s: %s puncte",
                        $row['eval_message'], $row['score']));
                $msg = "<span style=\"job-status-done\">$msg</span>";
                return format_link($url, $msg, false);
            }
            if ($row['status'] == 'processing') {
                // FIXME: animation? :)
                $msg = '<span class="job-status-processing">se evalueaza</span>';
                return format_link($url, $msg, false);
            }
            if ($row['status'] == 'waiting') {
                $msg = '<span style="job-stats-waiting">in asteptare</span>';
                return format_link($url, $msg, false);
            }
            log_error("Invalid job status");
        }

        // For the task column.
        function format_task_link($row) {
            if (!identity_can('job-view', $row)) {
                return "???";
            }
            return format_link(
                    url_textblock($row['task_page_name']),
                    $row['task_title']);
        }

        // For the detail column.
        function format_jobdetail_link($val) {
            return format_link(url_job_detail($val), "#$val");
        }

    $column_infos = array(
        array(
            'title' => 'ID',
            'key' => 'id',
            'valform' => 'format_jobdetail_link',
        ),
        array(
            'title' => 'Utilizator',
            'key' => 'username',
            'rowform' => create_function('$row',
                 'return format_user_tiny($row["user_name"], $row["user_fullname"]);'),
        ),
        array(
            'title' => 'Problema',
            'rowform' => 'format_task_link',
        ),
        array(
            'title' => 'Data',
            'key' => 'submit_time',
            'valform' => 'format_date',
        ),
        array(
            'title' => 'Stare (click pentru detalii)',
            'rowform' => 'format_state',
        ),
    );
    $options = array(
        'css_class' => 'monitor',
        'display_entries' => $view['display_entries'],
        'total_entries' => $view['total_entries'],
        'first_entry' => $view['first_entry'],
        'pager_style' => 'standard',
        'surround_pages' => 3,
    );

    print format_table($jobs, $column_infos, $options);
}

// Please don't use wiki_include() here because this is traffic
// intensive page.
?>

<p><?=format_link("documentatie/monitorul-de-evaluare", "Ce este si cum se foloseste")?> monitorul de evaluare?</p>

<?php include('footer.php'); ?>
