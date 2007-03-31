<?php 

require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "common/db/job.php");

// Job monitor controller.
function controller_monitor() {
    global $identity_user;

    $view = array();
    $view['filter'] = $_REQUEST;
    $view['user_name'] = $identity_user['username'];
    // First row.
    $options = pager_init_options(array('display_entries' => 25));

    $view['title'] = 'Monitorul de evaluare';
    $view['jobs'] = job_get_range($view['filter'], $options['first_entry'], $options['display_entries']);
    $view['total_entries'] = job_get_count($view['filter']);
    $view['first_entry'] = $options['first_entry'];
    $view['display_entries'] = $options['display_entries'];

    execute_view('views/monitor.php', $view);
}

?>
