<?php

require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");

if (!$display_only_table) {
    $view['head'] = '<script type="text/javascript" src="'.html_escape(url_static('js/monitor.js')).'"></script>';
    include(CUSTOM_THEME . 'header.php');
    $monitor_params = array('only_table' => 1, 'first_entry' => $first_entry, 'display_entries' => $view['display_entries']);
    $url_monitor = url_absolute(url_complex('monitor', $view['filters'] + $monitor_params));
?>
    <script type="text/javascript">
        Monitor_Url = '<?= $url_monitor ?>';
    </script>
<?php
}

if (!$display_only_table && (identity_can('job-reeval')
        || (request("task") && identity_can('task-reeval', task_get(request("task")))))
        && $view['total_entries'] <= IA_REEVAL_MAXJOBS) {
    echo '<form enctype="multipart/form-data" action="'.html_escape(url_reeval($view['filters'])).'"
               method="post" class="reeval" id="job_reeval" onsubmit="return confirm(\'Se vor reevalua '.
               html_escape($view['total_entries']).' job-uri! Continuăm?\')">';
    echo '<ul class="form hollyfix"><li id="field_submit">';
    echo '<input type="submit" class="button important" value="Re-evaluează!" id="form_reeval" />';
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
    $tabs['mine'] = format_link(url_monitor(array('user' => $user_name)), 'Soluțiile mele');
    if ($user_name == $user_filters) {
        $selected = 'mine';
    }
}

// all-jobs tab
$tabs['all'] = format_link(url_monitor(), 'Toate soluțiile');
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
    echo "<div class=\"notice\">Nici o soluție în coada de evaluare</div>";
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
                    $msg .= ": rezultate parțiale disponibile";
                }
            }
            $msg = "<span class=\"job-status-done\">$msg</span>";
            return format_link($url, $msg, false);
        }
        if ($row['status'] == 'processing') {
            $msg = '<img src="'.url_static('images/indicator.gif').'" />';
            $msg .= '<span class="job-status-processing">se evaluează';
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
            $msg = '<span class="job-stats-waiting">în așteptare</span>';
            return format_link($url, $msg, false);
        }

        if ($row['status'] == 'skipped') {
            $msg = '<span class="job-status-skipped">Submisie ignorată</span>';
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

    function format_skip($row) {
        if ($row['status'] == 'skipped') {
            return 'Ignorată';
        }

        if ($row['can_skip']) {
            $msg = format_tag(
                    "input",
                    "",
                    array(
                        "type" => "checkbox",
                        "class" => "skip_job",
                        "value" => $row['id']));
            $msg .= format_tag(
                    'a',
                    'Ignora!',
                    array(
                        "type" => "button",
                        "class" => "skip-job-link"));
            return $msg;
        }

        return "";
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
        array(
            'title' => 'Mărime',
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

    $can_skip_something = false;
    foreach ($jobs as $job) {
        if ($job['can_skip']) {
            $can_skip_something = true;
            break;
        }
    }

    if ($can_skip_something) {
        $column_infos[] = array('title' => 'Ignoră submisii', 'rowform' => 'format_skip');
    }

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

    if ($can_skip_something) {
        ?>
    <div class ="skip-job">
        <form id="skip-jobs-form" enctype="multipart/form-data" action="<?= html_escape(url_job_skip($view['filters'])) ?>"
               method="post" class="reeval" id="job_reeval">
        <input type="hidden" name="skipped-jobs" id="skipped-jobs"/>
        <input type="checkbox" id="skip-all-checkbox" />
        <input type="submit" class="button important" value="Ignora submisiile selectate"/>
        </form>
    </div>

        <?php
    }
}

// Please don't use wiki_include() here because this is traffic
// intensive page.

if (!$display_only_table) {
?>
    </div>

    <p>
        <input
            type="checkbox"
            <?php
              if (MONITOR_AUTOREFRESH) {
                echo 'checked="checked"';
              }
            ?>
            data-interval="<?php echo MONITOR_AUTOREFRESH_INTERVAL; ?>"
            id="autorefresh"
            onclick="Monitor_ToggleRefresh(this.checked)" />
        <label for="autorefresh">
            auto refresh monitor
        </label>
    </p>

    <p>
        <br/>
        <?= format_link("documentatie/monitorul-de-evaluare", "Ce este și cum se foloseste") ?>
        monitorul de evaluare.
    </p>

<?php
    include('footer.php');
}

?>
