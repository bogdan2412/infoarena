<?php

class ReportRoundsNeverFinished extends Report {

  function getDescription(): string {
    return 'Runde care ar fi trebuit să se termine';
  }

  function getVariable(): string {
    return 'Count.roundsNeverFinished';
  }

  function buildQuery(): ORM {
    // Unix_timestamp(datetime) expects datetime to be in the session
    // timezone. On the other hand, ia_round.start_time is UTC. Therefore,
    // convert it to the session timezone first, then to a timestamp.

    $conversionClause = 'convert_tz(r.start_time, "+00:00", @@session.time_zone)';
    $expectedFinish = sprintf('p.value * 3600 + unix_timestamp(%s)', $conversionClause);
    $timeClause = sprintf('%s < unix_timestamp()', $expectedFinish);

    return Model::factory('Round')
      ->table_alias('r')
      ->join('ia_parameter_value', [ 'r.id', '=', 'p.object_id' ], 'p')
      ->where('p.object_type', 'round')
      ->where('p.parameter_id', 'duration')
      ->where_not_equal('r.state', 'complete')
      ->where_raw($timeClause);
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }
}
