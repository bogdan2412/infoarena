<?php

class ScoreUserRoundTask extends Base {

  public static $_table = 'ia_score_user_round_task';

  static function getByUserIdRoundId(string $userId, string $roundId): array {
    return Model::factory('ScoreUserRoundTask')
      ->where('user_id', $userId)
      ->where('round_id', $roundId)
      ->find_many();
  }

  function updateScore(float $score): void {
    score_update($this->user_id, $this->task_id, $this->round_id, $score);
  }

  // Returns a map of roundId => taskId => userId => score. We keep both the
  // roundId and taskId in case multiple rounds use the same task.
  static function loadByRoundIds(array $roundIds): array {
    $records = Model::factory('ScoreUserRoundTask')
      ->where_in('round_id', $roundIds)
      ->find_many();

    $results = [];
    foreach ($records as $rec) {
      $results[$rec->round_id][$rec->task_id][$rec->user_id] = (float)$rec->score;
    }
    return $results;
  }
}
