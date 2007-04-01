<?php

require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");

include('header.php');

if (identity_can('job-reeval') && $view['total_entries'] <= IA_REEVAL_MAXJOBS) { 

    echo '<form enctype="multipart/form-data" action="'.htmlentities(url_reeval($view['filters'])).'" 
           method="post" class="reeval" id="job_reeval">';
    echo '<ul class="form hollyfix"><li id="field_submit">';
    echo '<input type="submit" class="button important" value="Re-evalueaza!" id="form_reeval" />';
    echo '</li></ul></form>';
}

echo '<h1>'.htmlentities($view['title']).'</h1>';

$tabs = array();
$selected = null;

// my-jobs tab
$user_filters = getattr($view['filters'], 'user');
if (!identity_is_anonymous()) {
    $tabs['mine'] = format_link(url_monitor(array('user' => $user_name)), 'Solutiile mele');
    if ($user_name == $user_filters) {
        $selected = 'mine';
    }
}

// all-jobs tab
$tabs['all'] = format_link(url_monitor(), 'Toate solutiile');
if (is_null($selected)) {
    $selected = 'all';
}

// custom-user filters tab
if ($user_filters && $user_name != $user_filters) {
    $tabs['custom'] = format_link(url_monitor(array('user' => $user_name)),
                                  'Trimise de "'.$user_filters.'"');
    $selected = 'custom';
}

if (1 < count($tabs)) {
    // mark 'active' tab
    $tabs[$selected] = array($tabs[$selected],
                             array('class' => 'active'));
    // display tabs
    echo format_ul($tabs, 'htabs');
}

if (!$jobs) {
    print "<div class=\"notice\">Nici o solutie in coada de evaluare</div>";
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
        array(
            'title' => 'ID',
            'key' => 'id',
            'valform' => 'format_jobdetail_link',
        ),
        array(
            'title' => 'Utilizator',
            'key' => 'username',
            'rowform' => create_function_cached('$row',
                 'return format_user_tiny($row["user_name"], $row["user_fullname"]);'),
        ),
        array(
            'title' => 'Problema',
            'rowform' => 'format_task_link',
        ),
        array(
            'title' => 'Runda',
            'rowform' => 'format_round_link',
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
	'show_count' => true,
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
