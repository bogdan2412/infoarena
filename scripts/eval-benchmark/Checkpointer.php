<?php

class Checkpointer {
  private string $dir;

  function __construct(string $dir) {
    $this->dir = rtrim($dir, '/');
    $this->createDirIfNeeded();
  }

  private function createDirIfNeeded() {
    if (!is_dir($this->dir)) {
      Log::info('Checkpoint directory [%s] does not exist, creating...', [$this->dir]);
      if (!@mkdir($this->dir, 0755)) {
        throw new BException('Could not create directory %s.', [$path]);
      }
    }
  }

  function writeTask(TaskBenchmark $tb): void {
    $task = $tb->getTask();
    $taskId = $task['id'];
    $cp = $tb->getCheckpoint();

    $this->writeTaskCheckpoint($taskId, $cp);
  }

  private function writeTaskCheckpoint(string $taskId, TaskCheckpoint $cp): void {
    $fileName = $this->getTaskFileName($taskId);
    $data = $cp->asArray();
    $this->writeAsJson($fileName, $data);
  }

  private function writeAsJson(string $name, array $data): void {
    $json = json_encode($data);
    if (!@file_put_contents($name, $json)) {
      throw new BException('Could not write to file %s.', [$name]);
    }
  }

  function readTask(string $taskId): ?TaskCheckpoint {
    $fileName = $this->getTaskFileName($taskId);
    if (!file_exists($fileName)) {
      return null;
    }

    $data = $this->readAndDecodeJson($fileName);
    return TaskCheckpoint::fromArray($data);
  }

  private function getTaskFileName(string $taskId): string {
    return sprintf('%s/%s.json', $this->dir, $taskId);
  }

  private function readAndDecodeJson($fileName) {
    $str = file_get_contents($fileName);
    if (!$str) {
      throw new BException('Could not read file %s.', [$fileName]);
    }
    return json_decode($str, true);
  }

}
