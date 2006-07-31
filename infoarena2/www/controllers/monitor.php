<?
function controller_monitor($filter, $suburl) {
    if (isset($identity_user) && $identity_user) {
        global $identity_user;
        $user_rows_per_page = $identity_user['lines_per_page'];
    }
    else {
        $user_rows_per_page = IA_DEFAULT_ROWS_PER_PAGE;
    }

    $view = array();
    $view['title'] = 'Monitor de evaluare';

    $page = getattr($_GET, 'page_num');
    if (!$page) {
        $page = 1;
    }

    $que = "";
    $view['turl'] = $filter;
    foreach ($filter as $key => $val) {
        $que .= "`" . $key . "` LIKE '" . db_escape($val) . "%' AND ";
    }
    $que = substr($que, 0, strlen($que)-4);

    $view['jobs'] = monitor_jobs_get_range(($page-1)*$user_rows_per_page,
                                           $user_rows_per_page, $que); 
    
    $view['suburl'] = $suburl;

    $view['page'] = $page;
    $view['row_max'] = monitor_jobs_count_range($que);
    $view['page_max'] = ceil($view['row_max'] / $user_rows_per_page);
    $view['rows'] = $user_rows_per_page;
    execute_view('views/monitor.php', $view);
}
?>