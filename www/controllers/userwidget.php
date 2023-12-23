<?php

/**
 * Displays an image with user statistics.
 *
 * @param  string $username
 * @return
 */
function controller_userwidget(string $username): void {
  $user = User::get_by_username($username);
  if (!$user) {
    FlashMessage::addError("Utilizatorul „{$username}” nu există.");
    Util::redirectToHome();
  }

  prepare_image($user);
}

function prepare_image(User $user): void {
  $numSolved = count($user->getArchiveTasks(true));
  $numFailed = count($user->getArchiveTasks(false));
  $successRate = $numSolved
    ? ($numSolved / ($numSolved + $numFailed))
    : 0;

  $ratingGroup = $user->getRatingGroup();
  $rgb = split_color($ratingGroup['colour']);

  $ratingText = sprintf('Rating: %d', $user->getScaledRating());
  $successText = sprintf('Succes: %0.2f%%', $successRate * 100);
  $solvedText = 'Probleme rezolvate: ' . $numSolved;
  $failedText = 'Probleme incercate: ' . $numFailed;

  $messages = [
    [  15, 42, $user->full_name ],
    [ 115,  5, $ratingText ],
    [  95, 19, $successText ],
    [  15, 54, $solvedText ],
    [  15, 65, $failedText ],
  ];

  create_and_print_image($rgb, $messages);
}

function create_and_print_image(array $rgb, array $messages): void {
  $img = imagecreate(200, 80);
  $bg_color = imagecolorallocate($img, 80, 80, 80);
  $fg_color = imagecolorallocate($img, 255, 255, 255);
  $line_color = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);

  foreach ($messages as $rec) {
    imagestring($img, 3, $rec[0], $rec[1], $rec[2], $fg_color);

  }
  imagesetthickness($img, 5);
  imageline($img, 0, 38, 200, 38, $line_color);

  header("Content-type: image/png");
  imagepng($img);

  imagecolordeallocate($img, $line_color);
  imagecolordeallocate($img, $fg_color);
  imagecolordeallocate($img, $bg_color);
  imagedestroy($img);
}

function split_color(string $hex): array {
  return [
    hexdec(substr($hex, 1, 2)),
    hexdec(substr($hex, 3, 2)),
    hexdec(substr($hex, 5, 2)),
  ];
}
