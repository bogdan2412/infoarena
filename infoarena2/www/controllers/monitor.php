<?
function controller_monitor($suburl) {
    $view = array();
    $view['title'] = 'Monitorul de evaluare';

    $view['jobs'] = monitor_jobs_get_range(0, 25);
    execute_view('views/monitor.php', $view);
}
?>