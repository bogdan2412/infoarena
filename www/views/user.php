<?php
require_once(Config::ROOT . 'common/tags.php');
require_once(Config::ROOT . 'www/format/format.php');
require_once(Config::ROOT . 'www/format/list.php');

$username = $user['username'];

require_once 'header.php';

// banned user notice and ban/unban buttons
if (Identity::isAdmin()) {
  $url = url_user_control($user['id']);
  if ($user['banned']) {
    echo '<a href="' . $url . '" class="user-control unban">deblochează</a>';
  } else {
    echo '<a href="' . $url . '" class="user-control ban">blochează</a>';
  }
}

// display user info across all user profile pages
echo Wiki::include($template_userheader, array('user' => $username));

// show profile tabs
$options = array(
  'view' => format_link(url_user_profile($username), 'Pagina personală'),
  'rating' => format_link(url_user_rating($username), 'Rating'),
  'stats' => format_link(url_user_stats($username), 'Statistici'),
);
// mark selected action with class 'active'
$options[$action] = array($options[$action], array('class' => 'active'));
echo format_ul($options, 'htabs');

if ('view' == $action) {
  // showing user's personal page
  include('textblock_header.php');

  // FIXME: duplicated code (see views/textblock_view.php)
  if (getattr($view, 'revision')) {
    include('revision_warning.php');
  }

  echo '<div class="wiki_text_block">';
  echo Wiki::processTextblock($textblock);
  echo '</div>';
}
else {
  // showing ratings / statistics
  echo Wiki::include($template, array('user' => $user['username']));
}

include('footer.php');
