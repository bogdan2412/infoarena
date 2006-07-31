<?
function controller_monitor($suburl) {
    global $identity_user;
    $user_rows_per_page = $identity_user['lines_per_page'];

    $view = array();
    $view['title'] = 'Monitor de evaluare';

    $page = getattr($_GET, 'page_num');
    if (!$page) {
        $page = 1;
    }

    if ($suburl) {
        $filter = "`round_id` LIKE '" . db_escape($suburl) . "%'";
        $view['jobs'] = monitor_jobs_get_range(($page-1)*$user_rows_per_page,
                                               $user_rows_per_page, $filter);
    }
    else {
        $view['jobs'] = monitor_jobs_get_range(($page-1)*$user_rows_per_page,
                                               $user_rows_per_page);
    }

    $view['suburl'] = $suburl;

    $view['page'] = $page;
    $view['row_max'] = monitor_jobs_count();
    $view['page_max'] = $view['row_max'] / $user_rows_per_page;
    $view['page_max'] = ceil($view['page_max']);
    $view['rows'] = $user_rows_per_page;
    execute_view('views/monitor.php', $view);
}
?>