<?php

require_once(IA_ROOT_DIR.'www/views/sitewide.php');
require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");

$view['head'] = '
<script type="text/javascript">
function RefreshPage() {
    window.location = window.location + " ";
}
setTimeout(RefreshPage, 2000);
</script>
<style type="text/css">
#sidebar, #header, #topnav, #breadcrumbs {
    display: none;
}
#main {
    margin-left: 0;
}
</style>
';

include(IA_ROOT_DIR.'www/views/header.php');

if (!$display_only_table) {
    echo '<h1>'.html_escape($view['title']).'</h1>';
}

echo "<div id=\"monitor-table\">";

if (!$jobs) {
    echo "<div class=\"notice\">Nici o solutie in coada de evaluare</div>";
} else {
    // For the score column.
    function format_state($row) {
        $url = url_job_detail($row['id']);
        if ($row['status'] == 'done') {
            $msg = html_escape(sprintf("%s: %s puncte",
                    $row['eval_message'], $row['score']));
            $msg = "<span style=\"job-status-done\">$msg</span>";
            return format_link($url, $msg, false);
        }
        if ($row['status'] == 'processing') {
            $img = '<img src="'.url_static('images/indicator.gif').'" />';
            $msg = $img.' <span class="job-status-processing">se evalueaza</span>';
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
        return format_link(
                url_textblock($row['task_page_name']),
                $row['task_title']);
    }

    // For the round column.
    function format_round_link($row) {
    //    return $row['round_id'];
        return format_link(
                url_textblock($row['round_page_name']),
                $row['round_title']);
    }

    // For the detail column.
    function format_jobdetail_link($val) {
        return format_link(url_job_detail($val), "#$val");
    }

    // For the reeval column.
    function format_reeval_link($job_id) {
    }

    $column_infos = array(
        /*array(
            'title' => 'ID',
            'key' => 'id',
            'valform' => 'format_jobdetail_link',
        ),*/
        array(
            'title' => 'Utilizator',
            'key' => 'username',
            'rowform' => function($row) {
                 return format_user_tiny($row['user_name'], $row['user_fullname']);
            },
        ),
        array(
            'title' => 'Problema',
            'rowform' => 'format_task_link',
        ),
        array(
            'title' => 'Runda',
            'rowform' => 'format_round_link',
        ),
        /*array(
            'title' => 'Data',
            'key' => 'submit_time',
            'valform' => 'format_date',
        ),*/
        array(
            'title' => 'Stare (click pentru detalii)',
            'rowform' => 'format_state',
        ),
    );
    $options = array(
        'css_class' => 'monitor',
        'show_count' => true,
        'display_entries' => $view['display_entries'],
        'total_entries' => $view['total_entries'],
        'first_entry' => $view['first_entry'],
        'pager_style' => 'standard',
        'surround_pages' => 3,
        'url_args' => $filters + array('page' => 'projector'),
    );

    print format_table($jobs, $column_infos, $options);
}

// Please don't use wiki_include() here because this is traffic
// intensive page.

?>
    </div>

<?php
include(IA_ROOT_DIR.'www/views/footer.php');
?>
