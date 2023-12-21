<?php

require_once Config::ROOT . 'common/db/user.php';
require_once Config::ROOT . 'common/db/score.php';
require_once Config::ROOT . 'common/user.php';
require_once Config::ROOT . 'www/views/utilities.php';

function controller_penalty_edit() {
  if (!Identity::isAdmin()) {
    Util::redirectToHome();
  }

  $roundId = Request::get('roundId');
  $userId = Request::get('userId');
  $round = Round::get_by_id($roundId);
  $user = User::get_by_id($userId);

  if (!$user || !$round) {
    FlashMessage::addError('Informații lipsă (userId sau roundId).');
    Util::redirectToHome();
  }

  // Load task scores and total score.
  // Note: this only loads tasks where the user actually submitted something.
  $scores = ScoreUserRoundTask::getByUserIdRoundId($userId, $roundId);
  $total = ScoreUserRound::getByUserIdRoundId($userId, $roundId);

  if (Request::isPost()) {
    foreach ($scores as $score) {
      $field = 'score_' . $score->task_id;
      $val = Request::getFloat($field, 0);
      $score->updateScore($val);
    }

    Util::redirect($round->getRankingsUrl());
  }

  Smart::assign([
    'round' => $round,
    'scores' => $scores,
    'total' => $total,
    'user' => $user,
  ]);
  Smart::display('penalty_edit.tpl');
}
