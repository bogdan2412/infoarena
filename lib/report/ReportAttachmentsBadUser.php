<?php

class ReportAttachmentsBadUser extends Report {

  function getDescription(): string {
    return 'Fișiere create de utilizatori inexistenți';
  }

  function getVariable(): string {
    return 'Count.attachmentsBadUser';
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
      ->left_outer_join('ia_user', [ 'a.user_id', '=', 'u.id' ], 'u')
      ->where_null('u.id');
  }

  function getLiveCount(): int {
    $query = $this->buildQuery();
    return $query->count();
  }

  function getAttachments(): array {
    $query = $this->buildQuery();
    return $query->find_many();
  }

}
