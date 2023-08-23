<?php

class ReportRoundsNullStartTime extends Report {

  function getDescription(): string {
    return 'Runde cu timp de început nul';
  }

  function getVariable(): string {
    return 'Count.roundsNullStartTime';
  }

  function getTemplateName(): string {
    return 'report/roundsNullStartTime.tpl';
  }

  function buildQuery(): ORM {
    return Model::factory('Round')
      ->where_null('start_time');
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

  function action(): void {
    $action = Request::get('report_action');

    switch ($action) {
      case 'round_delete':
        $roundId = Request::get('round_id');
        Round::deleteById($roundId);
        FlashMessage::addSuccess(sprintf('Am șters runda [%s].', $roundId));
        Util::redirectToSelf();
        break;

      default:
        FlashMessage::addError('Acțiune necunoscută.');
        Util::redirectToHome();
    }
  }
}
