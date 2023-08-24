<?php

class ReportRoundsBeforeBigBang extends Report {

  const BIG_BANG = '2012-09-01';

  function getDescription(): string {
    return 'Runde mai vechi decît site-ul';
  }

  function getVariable(): string {
    return 'Count.roundsBeforeBigBang';
  }

  function getTemplateName(): string {
    return 'report/roundsBeforeBigBang.tpl';
  }

  function getSupportedActions(): array {
    return [ 'round_delete' ];
  }

  function buildQuery(): ORM {
    return Model::factory('Round')
      ->where_not_equal('type', 'archive')
      ->where_lt('start_time', self::BIG_BANG);
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getRounds(): array {
    $query = $this->buildQuery();
    return $query
      ->order_by_asc('id')
      ->find_many();
  }

}
