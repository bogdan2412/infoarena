<?
function controller_monitor($suburl) {
    $user_rows_per_page = 25; // TODO FIXME: make this user-selectable

    $view = array();
    $view['title'] = 'Monitorul de evaluare';

    $start = getattr($_GET, 'start');
    if (!$start) {
        $start = 0;
    }

    $view['jobs'] = monitor_jobs_get_range($start, $user_rows_per_page);
    $view['start'] = $start;
    $view['row_max'] = monitor_jobs_count();
    $view['rows'] = $user_rows_per_page;
    execute_view('views/monitor.php', $view);
}
?>