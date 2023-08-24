<?php

class ReportAttachmentsBadPage extends Report {

  function getDescription(): string {
    return 'Fișiere aparținînd de pagini inexistente';
  }

  function getVariable(): string {
    return 'Count.attachmentsBadPage';
  }

  function getTemplateName(): string {
    return 'report/attachmentsBadForeignKeys.tpl';
  }

  function getSupportedActions(): array {
    return [ 'attachment_delete' ];
  }

  function buildQuery(): ORM {
    return Model::factory('Attachment')
      ->table_alias('a')
      ->select('a.*')
      ->left_outer_join('ia_textblock', [ 'a.page', '=', 'tb.name' ], 'tb')
      ->where_null('tb.name');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getAttachments(): array {
    $query = $this->buildQuery();
    return $query
      ->order_by_asc('page')
      ->order_by_asc('name')
      ->find_many();
  }

}
