<?

require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "common/db/job.php");

// Job monitor controller.
function controller_monitor() {
    global $identity_user;

    $view = array();
    $view['task_filter'] = request('task', null);
    $view['user_filter'] = request('user', null);
    $view['user_security'] = getattr($identity_user, 'security_level', 'anonymous');
    $view['user_name'] = $identity_user['username'];
    // First row.
    $options = pager_init_options();

    $view['title'] = 'Monitorul de evaluare';
    $view['jobs'] = job_get_range($options['first_entry'], $options['display_entries'],
                    $view['task_filter'], $view['user_filter']); 
    $view['first_entry'] = $options['first_entry'];
    $view['total_entries'] = job_get_count($view['task_filter'], $view['user_filter']);
    $view['display_entries'] = $options['display_entries'];

    execute_view('views/monitor.php', $view);
}

?>
