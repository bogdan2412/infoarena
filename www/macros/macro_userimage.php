<?php

function macro_userimage($args) {
  $username = $args['user'] ?? '';
  $user = User::get_by_username($username);
  if (!$user) {
    return macro_error("Utilizatorul „{$username}” nu există.");
  }

  $size = $args['size'] ?? '';
  if (!isset(Config::GEOMETRY[$size])) {
    return macro_error("Nu înțeleg dimensiunea „{$size}”.");
  }

  Smart::assign([
    'user' => $user,
    'size' => $size,
  ]);
  return Smart::fetch('bits/userImage.tpl');
}
