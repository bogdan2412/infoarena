<?php

function macro_archivetasks(array $args): string {
  $username = $args['user'] ?? '';
  $user = User::get_by_username($username);
  if (!$user) {
    return macro_error("Utilizatorul „{$username}” nu există.");
  }

  $solved = ($args['solved'] ?? 'false') == 'true';

  $tasks = $user->getArchiveTasks($solved);

  Smart::assign([
    'solved' => $solved,
    'tasks' => $tasks,
  ]);
  return Smart::fetch('macro/archiveTasks.tpl');
}
