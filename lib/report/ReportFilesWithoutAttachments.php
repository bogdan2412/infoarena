<?php

class ReportFilesWithoutAttachments extends Report {

  const SPECIAL_FILES = [ '.', '..' ];

  // Process the attachment in batches to keep the memory footprint small.
  const BATCH_SIZE = 2000;
  private array $expectedFiles = [];
  private array $files = [];
  private bool $computed = false;

  function getDescription(): string {
    return 'Fișiere pe disc care nu aparțin niciunui fișier atașat';
  }

  function getVariable(): string {
    return 'Count.filesWithoutAttachments';
  }

  function getTemplateName(): string {
    return 'report/filesWithoutAttachments.tpl';
  }

  function getSupportedActions(): array {
    return [ 'cleanup' ];
  }

  function getLiveCount(): int {
    $this->compute();
    return count($this->files);
  }

  function getFiles(): array {
    $this->compute();
    return $this->files;
  }

  function cleanup(): void {
    $this->compute();
    foreach ($this->files as $file) {
      unlink($file);
    }
    FlashMessage::addSuccess('Am șters toate fișierele orfane.');
  }

  private function compute(): void {
    if (!$this->computed) {
      $this->collectExpectedFiles();
      $this->processActualFiles();
      $this->computed = true;
    }
  }

  private function collectExpectedFiles(): void {
    $offset = 0;
    do {
      $batch = $this->loadNextBatch($offset);
      $this->collectFromBatch($batch);
      $offset += self::BATCH_SIZE;
    } while (count($batch));

    $this->expectedFiles = array_fill_keys($this->expectedFiles, true);
  }

  private function loadNextBatch(int $offset): array {
    return Model::factory('Attachment')
      ->order_by_asc('id')
      ->offset($offset)
      ->limit(self::BATCH_SIZE)
      ->find_many();
  }

  private function collectFromBatch(array $batch): void {
    foreach ($batch as $attachment) {
      $arr = $attachment->as_array();
      $path = attachment_get_filepath($arr);
      $this->expectedFiles[] = $path;

      $avatarFiles = $attachment->getAvatarFiles(); // possibly empty
      array_push($this->expectedFiles, ...$avatarFiles);
    }
  }

  private function processActualFiles(): void {
    $this->processFilesInDirectory(Attachment::getDirectory());
    foreach (Attachment::getAvatarDirectories() as $dir) {
      $this->processFilesInDirectory($dir);
    }
  }

  private function processFilesInDirectory(string $directory): void {
    $files = $this->scandirExcludeSpecialFiles($directory);
    foreach ($files as $file) {
      $full = $directory . $file;
      if (!isset($this->expectedFiles[$full])) {
        $this->files[] = $full;
      }
    }
  }

  private function scandirExcludeSpecialFiles(string $directory): array {
    return array_diff(
      scandir($directory),
      self::SPECIAL_FILES,
    );
  }

}
