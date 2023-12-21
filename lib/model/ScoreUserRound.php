<?php

class ScoreUserRound extends Base {

  public static $_table = 'ia_score_user_round';

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
