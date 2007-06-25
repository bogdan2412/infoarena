<?php 

require_once(IA_ROOT_DIR."www/format/pager.php");
require_once(IA_ROOT_DIR."common/db/job.php");
require_once(IA_ROOT_DIR."www/controllers/job_filters.php");

// Job monitor controller for projector display.
function controller_projector() {
    global $identity_user;

    $view = array();
    $view['filters'] = job_get_filters();
    $view['user_name'] = getattr($identity_user, 'username');
    // First row.
    // FIXME: shouldn't this constant be in config.php? 
    $options = pager_init_options(array('display_entries' => 20));

    $view['title'] = 'Monitorul de evaluare';
    $view['jobs'] = job_get_range($view['filters'], $options['first_entry'], $options['display_entries']);
    $view['total_entries'] = job_get_count($view['filters']);
    $view['first_entry'] = $options['first_entry'];
    $view['display_entries'] = $options['display_entries'];
    $view['display_only_table'] = request('only_table', false);

    execute_view('views/projector.php', $view);
}

?>
