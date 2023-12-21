<?php

class ScoreUserRound extends Base {

  public static $_table = 'ia_score_user_round';

  static function getByUserIdRoundId(string $userId, string $roundId): ?ScoreUserRound {
    return Model::factory('ScoreUserRound')
      ->where('user_id', $userId)
      ->where('round_id', $roundId)
      ->find_one();
  }

  // Returns an array of [ 'userId', 'total' ] pairs, sorted by total
  // descending.
  static function loadTotalsByRoundIds(array $roundIds): array {
    return Model::factory('ScoreUserRound')
      ->select('user_id', 'userId')
      ->select_expr('sum(score)', 'total')
      ->where_in('round_id', $roundIds)
      ->group_by('user_id')
      ->order_by_desc('total')
      ->find_array();
  }

  // Returns a map of roundId => userId => score
  static function loadByRoundIds(array $roundIds): array {
    $records = Model::factory('ScoreUserRound')
      ->where_in('round_id', $roundIds)
      ->find_many();

    $results = [];
    foreach ($records as $rec) {
      $results[$rec->round_id][$rec->user_id] = (float)$rec->score;
    }
    return $results;
  }
}
