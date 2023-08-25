<?php

class ReportAttachmentsWithoutFiles extends Report {

  // Process the attachment in batches to keep the memory footprint small.
  const BATCH_SIZE = 3000;

  function getDescription(): string {
    return 'Fișiere atașate ale căror fișiere pe disc lipsesc';
  }

  function getVariable(): string {
    return 'Count.attachmentsWithoutFiles';
  }

  function getTemplateName(): string {
    return 'report/attachmentsBadForeignKeys.tpl';
  }

  function getSupportedActions(): array {
    return [ 'attachment_delete' ];
  }

  function getLiveCount(): int {
    return count($this->getAttachments());
  }

  function getAttachments(): array {
    $results = [];

    $offset = 0;
    do {
      $batch = $this->loadNextBatch($offset);
      if (count($batch)) {
        $filtered = $this->filterBatch($batch);
        array_push($results, ...$filtered);
      }
      $offset += self::BATCH_SIZE;
    } while (count($batch));

    return $results;
  }

  private function loadNextBatch(int $offset): array {
    return Model::factory('Attachment')
      ->order_by_asc('id')
      ->offset($offset)
      ->limit(self::BATCH_SIZE)
      ->find_many();
  }

  private function filterBatch(array $batch): array {
    $results = [];

    foreach ($batch as $attachment) {
      $arr = $attachment->as_array();
      $path = attachment_get_filepath($arr);
      if (!file_exists($path)) {
        $results[] = $attachment;
      }
    }

    return $results;
  }

}
