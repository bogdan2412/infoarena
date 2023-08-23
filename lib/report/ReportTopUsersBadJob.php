<?php

class ReportTopUsersBadJob extends Report {

  function getDescription(): string {
    return 'Top - joburi inexistente';
  }

  function getVariable(): string {
    return 'Count.topUsersBadJob';
  }

  function getTemplateName(): string {
    return 'report/topUsersBadForeignKeys.tpl';
  }

  function getSupportedActions(): array {
    return [ 'cleanup' ];
  }

  function buildQuery(): ORM {
    return Model::factory('ScoreTaskTopUsers')
      ->where_raw('job_id not in (select id from ia_job)');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getTop(): array {
    $query = $this->buildQuery();
    return $query
      ->order_by_asc('job_id')
      ->find_many();
  }

  function cleanup(): void {
    $this->buildQuery()->delete_many();
    FlashMessage::addSuccess('Am șters toate înregistrările.');
  }

}
