<?

// Prints detailed user information, mostly for admins.
// For an url of userinfo/$user/$page it displays template/userinfo/$page.
// $page can be missing.
// It displays template/userinfo for the current user.
function controller_userinfo($pageid) {
    $split = explode('/', $pageid, 2);
    if (!isset($split[0])) {
        flash_error("URL invalid");
        redirect(url('home'));
    }

    $user = user_get_by_username($split[0]);
    if (!$user) {
        flash_error("Utilizator inexistent");
        redirect(url('home'));
    }

    if (isset($split[1])) {
        $page_name = "template/userinfo/$subpageid";
    } else {
        $page_name = "template/userinfo";
    }

    // FIXME: per-page?
    identity_require('user-viewinfo', $user);

    //log_print($page_name);
    // Hack page.
    $page = textblock_get_revision($page_name);
    if (!$page) {
        flash_error("Teapa");
        redirect(url('home'));
    }

    $page['title'] = str_replace('%user_id%', $user['username'], $page['title']);
    $page['text'] = str_replace('%user_id%', $user['username'], $page['text']);

    // Build view.
    // FIXME: This is really hacky, MUST cleanup textblock view.
    $view = array();
    $view['title'] = $page['title'];
    $view['page_name'] = $page_name;
    $view['textblock'] = $page;
    execute_view_die('views/userinfo.php', $view);
}

?>
