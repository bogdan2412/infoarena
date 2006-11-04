<?

// Job monitor controller.
function controller_monitor() {
    if (isset($identity_user) && $identity_user) {
        global $identity_user;
        $display_rows = $identity_user['lines_per_page'];
    } else {
        $display_rows = IA_DEFAULT_ROWS_PER_PAGE;
    }

    $view = array();

    $first_row = request('start', 0);
    $view['jobs'] = monitor_jobs_get_range($first_row, $display_rows); 

    $view['title'] = 'Monitor de evaluare';
    $view['url_page'] = 'monitor';
    $view['url_args'] = $_GET;
    $view['first_row'] = $first_row;
    $view['total_rows'] = monitor_jobs_get_count();
    $view['display_rows'] = $display_rows;
    execute_view('views/monitor.php', $view);
}

?>
