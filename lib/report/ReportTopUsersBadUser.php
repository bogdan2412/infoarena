<?php

class ReportTopUsersBadUser extends Report {

  function getDescription(): string {
    return 'Top - utilizatori inexistenți';
  }

  function getVariable(): string {
    return 'Count.topUsersBadUser';
  }

  function getTemplateName(): string {
    return 'report/topUsersBadForeignKeys.tpl';
  }

  function getSupportedActions(): array {
    return [ 'cleanup' ];
  }

  function buildQuery(): ORM {
    return Model::factory('ScoreTaskTopUsers')
      ->where_raw('user_id not in (select id from ia_user)');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getTop(): array {
    $query = $this->buildQuery();
    return $query
      ->order_by_asc('user_id')
      ->find_many();
  }

  function cleanup(): void {
    $this->buildQuery()->delete_many();
    FlashMessage::addSuccess('Am șters toate înregistrările.');
  }

}
