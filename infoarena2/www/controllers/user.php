<?

require_once(IA_ROOT."common/textblock.php");
require_once(IA_ROOT."common/db/textblock.php");
require_once(IA_ROOT."common/db/user.php");

// View user profile (personal page, rating evolution, statistics)
// $action is one of (view | rating | stats)
function controller_user_view($username, $action) {
    // validate username
    $user = user_get_by_username($username);
    if (!$user) {
        flash_error("Utilizator inexistent");
        redirect(url('home'));
    }

    // Build view.
    $page_name = TB_USER_PREFIX.$user['username'];
    $view = array(
        'title' => $user['full_name'].' ('.$user['username'].')',
        'page_name' => $page_name,
        'action' => $action,
        'user' => $user,
        'template_userheader' => 'template/userheader',
    );

    switch ($action) {
        case 'view':
            // view personal page
            $textblock = textblock_get_revision($page_name);
            log_assert($textblock);
            $view['textblock'] = $textblock;
            break;

        case 'rating':
            // view rating evolution
            $view['template'] = 'template/userrating';
            break;

        case 'stats':
            // view user statistics
            $view['template'] = 'template/userstats';
            break;

        default:
            log_error('Invalid user profile action: '.$action);
    }

    // View
    execute_view_die('views/user.php', $view);
}

?>
