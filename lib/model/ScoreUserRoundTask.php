<?php

class ScoreUserRoundTask extends Base {

  public static $_table = 'ia_score_user_round_task';

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
