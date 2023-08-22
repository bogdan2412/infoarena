<?php

class ReportRoundsNullStartTime extends Report {

  function getDescription(): string {
    return 'Runde cu timp de început nul';
  }

  function getVariable(): string {
    return 'Count.roundsNullStartTime';
  }

  function buildQuery(): ORM {
    return Model::factory('Round')
      ->where_null('start_time');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }
}
