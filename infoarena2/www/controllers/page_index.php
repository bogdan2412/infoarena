<?php

function controller_page_index($page_name)
{
    identity_require('specialpage-index');
    $show_user = request('show_user', 1);
    $prefix = request('prefix', '');
    $view = array();
    $view['page_name'] = $page_name;
    $view['page'] = textblock_get_revision($page_name);

    if ($prefix) {
        $view['title'] = "Lista paginilor cu prefixul $prefix";
    } else {
        $view['title'] = "Lista tuturor paginilor de wiki";
    }

    $view['page_list'] = textblock_get_list_by_prefix($prefix, false, $show_user);

    execute_view_die('views/page_index.php', $view);
}

?>
