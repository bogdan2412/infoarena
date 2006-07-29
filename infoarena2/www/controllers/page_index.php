<?php
function controller_page_index($page_name) {
    identity_require('page_index');
    $show_user = request('show_user', 1);
    $view = array();
    $view['page_name'] = $page_name;
    $view['page'] = textblock_get_revision($page_name);
    $view['title'] = 'Pagini'.($page_name ? ' din '.$page_name : '');
    $view['page_list'] = !$show_user ? textblock_get_names($page_name) :
                         textblock_get_names_with_user($page_name);
    execute_view_die('views/page_index.php', $view);
}
?>