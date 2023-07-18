<?php

require_once(Config::ROOT."common/textblock.php");
require_once(Config::ROOT."common/db/textblock.php");
require_once(Config::ROOT."common/db/user.php");

// View user profile (personal page, rating evolution, statistics)
// $action is one of (view | rating | stats)
function controller_user_view($username, $action, $rev_num = null) {
  // validate username
  $user = user_get_by_username($username);
  if (!$user) {
    FlashMessage::addError("Utilizator inexistent.");
    redirect(url_home());
  }

  // Build view.
  $page_name = Config::USER_TEXTBLOCK_PREFIX.$user['username'];
  $view = array(
    'title' => $user['full_name'].' ('.$user['username'].')',
    'page_name' => $page_name,
    'action' => $action,
    'user' => $user,
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
          FlashMessage::addError('Revizia "' . $rev_num . '" este invalidă.');
          redirect(url_textblock($page_name));
        } else {
          $rev_num = (int)$rev_num;
        }
        Identity::enforceViewTextblock($textblock);
        $textblock = textblock_get_revision($page_name, $rev_num);

        if (!$textblock) {
          FlashMessage::addError('Revizia "' . $rev_num . '" nu există.');
          redirect(url_textblock($page_name));
        }
      } else {
        Identity::enforceViewTextblock($textblock);
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

  if (Identity::isAdmin() && $user['banned']) {
    FlashMessage::addWarning('Acest utilizator este blocat.');
  }

  // View
  execute_view_die('views/user.php', $view);
}
