<?php

class Checkpointer {
  private string $dir;

  function __construct(string $dir) {
    $this->dir = $dir;
    $this->checkNonemptyDirName();
    $this->createDirIfNeeded();
  }

  private function checkNonemptyDirName() {
    if (!$this->dir) {
      throw new BException(
        "Please specify a checkpoint directory with -c <dir>, e.g. -c /tmp/benchmark.\n" .
        'This allows us to save/restore progress.');
    }
  }

  private function createDirIfNeeded() {
    if (!file_exists($this->dir)) {
      Log::info('Checkpoint directory [%s] does not exist, creating...', [$this->dir]);
      if (!@mkdir($this->dir, 0755)) {
        throw new BException('Could not create directory %s.', [$this->dir]);
      }
    }
  }
}
