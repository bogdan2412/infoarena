<?php

class RoundTask extends Base {

  public static $_table = 'ia_round_task';

  static function countByRoundId(string $roundId): int {
    return Model::factory('RoundTask')
      ->where('round_id', $roundId)
      ->count();
  }

}
