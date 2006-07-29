<?php
// display history list
function controller_textblock_history($page_name) {
    $page = textblock_get_revision($page_name);
    if ($page) {
        identity_require('history', $page);
    }
    else {
        flash_error("Pagina nu exista");
        redirect(url(''));
    }

    $view = array();
    $view['page_name'] = $page_name;
    $view['title'] = 'Istoria paginii '.$page_name;
    $view['count'] = textblock_get_revision_count();
    $view['page_list'] = textblock_get_revisions_without_content();
    execute_view_die('views/history.php', $view);
}
?>