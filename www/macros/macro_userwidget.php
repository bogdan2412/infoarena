<?php

/**
 * Returns an image showing user statistics.
 *
 * @param string $user
 * @return string Rendered HTML
 *
 */
function macro_userwidget($args) {
  $username = $args['user'] ?? '';
  $user = User::get_by_username($username);
  if (!$user) {
    return macro_error("Utilizator inexistent: „{$username}”.");
  }

  Smart::assign('user', $user);
  return Smart::fetch('macro/userWidget.tpl');
}
