<?php

// Display a link to an user.
// Includes avatar, etc.
//
// Args:
//      user(required): user id.
//      type: link(default), tiny, normal, etc.
function macro_user($args) {
  $username = $args['user'] ?? '';
  $user = User::get_by_username($username);
  if (!$user) {
    return macro_error('Utilizator inexistent.');
  }

  $type = $args['type'] ?? 'normal';
  $template =  '';
  switch($type) {
    case 'tiny': $template = 'bits/userTiny.tpl'; break;
    case 'normal': $template = 'bits/userNormal.tpl'; break;
  }
  if (!$template) {
    return macro_error("Stil necunoscut: „{$type}”.");
  }

  Smart::assign([
    'user' => $user,
    'showRating' => true,
  ]);
  return Smart::fetch($template);
}
