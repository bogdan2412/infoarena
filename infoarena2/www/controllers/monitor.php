<?

// Job monitor controller.
function controller_monitor() {
    if (isset($identity_user) && $identity_user) {
        global $identity_user;
        $display_rows = $identity_user['lines_per_page'];
    } else {
        $display_rows = IA_DEFAULT_ROWS_PER_PAGE;
    }

    // First row.
    $first_row = request('start', 0);
    if ($first_row < 0) {
        flash_error("Numar de pagina invalid.");
        $first_row = 0;
    }

    $view = array();

    $view['jobs'] = job_get_range($first_row, $display_rows); 
    $view['title'] = 'Monitor de evaluare';
    $view['url_page'] = 'monitor';
    $view['url_args'] = $_GET;

    $first_row = request('start', 0);
    if ($first_row < 0) {
        flash_error("Numar de pagina invalid.");
        $first_row = 0;
    }
    $view['first_row'] = $first_row;
    $view['total_rows'] = job_get_count();
    $view['display_rows'] = $display_rows;
    execute_view('views/monitor.php', $view);
}

?>
