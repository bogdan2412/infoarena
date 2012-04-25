<?php

require_once(IA_ROOT_DIR."common/textblock.php");
require_once(IA_ROOT_DIR."common/db/textblock.php");
require_once(IA_ROOT_DIR."common/db/user.php");

// View user profile (personal page, rating evolution, statistics)
// $action is one of (view | rating | stats)
function controller_user_view($username, $action, $rev_num = null) {
    // validate username
    $user = user_get_by_username($username);
    if (!$user) {
        flash_error("Utilizator inexistent");
        redirect(url_home());
    }

    // Build view.
    $page_name = IA_USER_TEXTBLOCK_PREFIX.$user['username'];
    $view = array(
        'title' => $user['full_name'].' ('.$user['username'].')',
        'page_name' => $page_name,
        'action' => $action,
        'user' => $user,
        'topnav_select' => 'profile',
        'template_userheader' => 'template/userheader',
    );

    switch ($action) {
        case 'view':
            // View personal page
            $textblock = textblock_get_revision($page_name);
            // Checks if $rev_num is the latest.
            $rev_count = textblock_get_revision_count($page_name);
            if ($rev_num && $rev_num != $rev_count) {
                if (!is_numeric($rev_num) || (int)$rev_num < 1) {
                    flash_error('Revizia "' . $rev_num . '" este invalida.');
                    redirect(url_textblock($page_name));
                } else {
                    $rev_num = (int)$rev_num;
                }
                identity_require("textblock-history", $textblock);
                $textblock = textblock_get_revision($page_name, $rev_num);

                if (!$textblock) {
                    flash_error('Revizia "' . $rev_num . '" nu exista.');
                    redirect(url_textblock($page_name));
                }
            } else {
                identity_require("textblock-view", $textblock);
            }
            log_assert_valid(textblock_validate($textblock));
            $view['revision'] = $rev_num;
            $view['revision_count'] = $rev_count;
            $view['textblock'] = $textblock;
            $view['title'] = $textblock['title'];
            break;

        case 'rating':
            // view rating evolution
            $view['template'] = 'template/userrating';
            $view['title'] = 'Rating '.$view['title'];
            break;

        case 'stats':
            // view user statistics
            $view['template'] = 'template/userstats';
            $view['title'] = 'Statistici '.$view['title'];
            break;

        default:
            log_error('Invalid user profile action: '.$action);
    }

    // View
    execute_view_die('views/user.php', $view);
}
