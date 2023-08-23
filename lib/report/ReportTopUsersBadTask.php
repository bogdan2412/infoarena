<?php

class ReportTopUsersBadTask extends Report {

  function getDescription(): string {
    return 'Top - probleme inexistente';
  }

  function getVariable(): string {
    return 'Count.topUsersBadTask';
  }

  function getTemplateName(): string {
    return 'report/topUsersBadTask.tpl';
  }

  function getSupportedActions(): array {
    return [ 'cleanup' ];
  }

  function buildQuery(): ORM {
    return Model::factory('ScoreTaskTopUsers')
      ->where_raw('task_id not in (select id from ia_task)');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getTop(): array {
    $query = $this->buildQuery();
    return $query
      ->order_by_asc('task_id')
      ->find_many();
  }

  function cleanup(): void {
    $this->buildQuery()->delete_many();
    FlashMessage::addSuccess('Am șters toate înregistrările.');
  }

}
