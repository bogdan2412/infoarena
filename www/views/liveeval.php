<?php

require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");

$view['head'] = '<script src="'.
    html_escape(url_static('js/liveeval.js')).'"></script>';
$view['head'] = $view['head'] . '
<style type="text/css">
#sidebar, #header, #topnav, #breadcrumbs {
    display: none;
}
#main {
    margin-left: 0;
}

.hidden-score {
    display: none;
}

.processing {
    display: none;
}

</style>
';

include(CUSTOM_THEME . 'header.php');
$url_monitor = "http://www.infoarena.ro/liveeval";
?>
    <script>
        Monitor_Url = '<?= $url_monitor ?>';
    </script>
<?php

echo '<h1>'.html_escape($view['title']).'</h1>';

$tabs = array();
$selected = null;

echo "<div id=\"monitor-table\">";

if (!$jobs) {
    echo "<div class=\"notice\">Nici o soluție în coada de evaluare.</div>";
} else {
    // For the score column.
    function format_state($row) {
        $url = url_job_detail($row['id']);
        $msg = "";
        $hiddenMsg = html_escape(sprintf("%s: %s puncte",
                $row['eval_message'], $row['score']));
        $hiddenMsg = "<span class=\"job-status-done\">$hiddenMsg</span>";
        $hiddenMsg = '<div class="hidden-score">'.
                    format_link($url, $hiddenMsg, false) .
                    '</div>';

        $processing = '<img src="'.url_static('images/indicator.gif').'">';
        $processing .= '<span class="job-status-processing">se evaluează';
        $processing .= '</span>';
        $processing = '<div class="processing">'.
                        format_link($url, $processing, false).
                      '</div>';


        $msg = '<span class="job-stats-waiting">în așteptare</span>';
        $msg = '<div class="lie-score">'.
                    format_link($url, $msg, false).
               '</div>';
        return $hiddenMsg . $processing . $msg;
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
        return format_date($val, 'd MMM yyyy HH:mm:ss');
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
            'rowform' => function($row) {
                return format_user_tiny($row['user_name'], $row['user_fullname']);
            },
        ),
        array(
            'title' => 'Problemă',
            'rowform' => 'format_task_link',
        ),
        array(
            'title' => 'Rundă',
            'rowform' => 'format_round_link',
        ),
        array(
            'title' => 'Mărime',
            'rowform' => 'format_size',
        ),
        array(
            'title' => 'Dată',
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
        'pager_style' => 'standard',
        'first_entry' => 0,
        'display_entries' => count($jobs),
        'total_entries' => count($jobs)
    );

    print format_table($jobs, $column_infos, $options);

    print '<input type="submit"
                  id="liveeval"
                  class="button important"
                  value="Evaluează"
                  style="float: right"><br><br>';
}

// Please don't use Wiki::include() here because this is traffic
// intensive page.

include('footer.php');
