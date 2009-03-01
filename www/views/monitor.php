<?php

require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");

if (!$display_only_table) {
    $view['head'] = '<script type="text/javascript" src="'.html_escape(url_static('js/monitor.js')).'"></script>';
    include('header.php');
}

if (!$display_only_table && identity_can('job-reeval') && $view['total_entries'] <= IA_REEVAL_MAXJOBS) {
    echo '<form enctype="multipart/form-data" action="'.html_escape(url_reeval($view['filters'])).'"
               method="post" class="reeval" id="job_reeval" onsubmit="return confirm(\'Se vor reevalua '.
               html_escape($view['total_entries']).' job-uri! Continuam?\')">';
    echo '<ul class="form hollyfix"><li id="field_submit">';
    echo '<input type="submit" class="button important" value="Re-evalueaza!" id="form_reeval" />';
    echo '</li></ul></form>';
}

if (!$display_only_table) {
    echo '<h1>'.html_escape($view['title']).'</h1>';
}

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

if (!$display_only_table && 1 < count($tabs)) {
    // mark 'active' tab
    $tabs[$selected] = array($tabs[$selected],
                             array('class' => 'active'));
    // display tabs
    echo format_ul($tabs, 'htabs');
}

if (!$display_only_table) {
    echo "<div id=\"monitor-table\">";
}

if (!$jobs) {
    echo "<div class=\"notice\">Nici o solutie in coada de evaluare</div>";
} else {
    // For the score column.
    function format_state($row) {
        $url = url_job_detail($row['id']);
        if ($row['status'] == 'done') {
            if (!is_null($row['score'])) {
                $msg = html_escape(sprintf("%s: %s puncte",
                    $row['eval_message'], $row['score']));
            } else {
                $msg = html_escape($row['eval_message']);
                if (isset($row["feedback_available"])) {
                    $msg .= ": rezultate partiale disponibile";
                }
            }
            $msg = "<span style=\"job-status-done\">$msg</span>";
            return format_link($url, $msg, false);
        }
        if ($row['status'] == 'processing') {
            $msg = '<img src="'.url_static('images/indicator.gif').'" />';
            $msg .= '<span class="job-status-processing">se evalueaza';
//            if (array_key_exists('done_tests', $row)) {
//                $done = $row['done_tests'];
//                $total = $row['total_tests'];
//                if ($done < $total) {
//                    ++$done;
//                }
//                $msg .= '(' . $done . '/' . $total . ')';
//            }
            $msg .= '</span>';
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
        if (!identity_can("job-view", $row)){
            return "...";
        }
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

    // For the date column.
    function format_short_date($val) {
        return format_date($val, '%e %b %y %H:%M:%S');
    }

    // For the size column.
    function format_size($row) {
        if (is_null($row['job_size'])) {
            return "...";
        }
        $size = sprintf("%.2f", $row['job_size']/1024)." kb";
        if (identity_can('job-view-source', $row)) {
            return format_link(url_job_view_source($row['id']), $size);
        }
        return $size;
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
            'title' => 'Marime',
            'rowform' => 'format_size',
        ),
        array(
            'title' => 'Data',
            'key' => 'submit_time',
            'valform' => 'format_short_date',
        ),
        array(
            'title' => 'Stare',
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
        'url_args' => $view['filters'] + array('page' => 'monitor'),
    );

    print format_table($jobs, $column_infos, $options);
}

// Please don't use wiki_include() here because this is traffic
// intensive page.

if (!$display_only_table) {
    $monitor_params = array('only_table' => 1, 'first_entry' => $first_entry, 'display_entries' => $view['display_entries']);
    $url_monitor = url_absolute(url_complex('monitor', $view['filters'] + $monitor_params));

?>
    </div>

    <script type="text/javascript">
        Monitor_Url = '<?= $url_monitor ?>';
    </script>

    <p>
        <input type="checkbox" checked="checked" id="autorefresh" onclick="Monitor_ToggleRefresh(this.checked)" />
        <label for="autorefresh">
            auto refresh monitor
        </label>
    </p>

    <p>
        <br/>
        <?= format_link("documentatie/monitorul-de-evaluare", "Ce este si cum se foloseste") ?>
        monitorul de evaluare.
    </p>

<?php
    include('footer.php');
}

?>
