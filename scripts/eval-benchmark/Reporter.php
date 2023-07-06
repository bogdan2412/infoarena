<?php

class Reporter {
  private array $tasks;
  private Database $db;
  private Checkpointer $checkpointer;
  private bool $sqlFormat;

  private array $skippedTasks = [];
  private array $unhandledTasks = [];
  private array $unchangedTasks = [];

  function __construct(array $tasks, Database $db, Checkpointer $checkpointer, bool $sqlFormat) {
    $this->tasks = $tasks;
    $this->db = $db;
    $this->checkpointer = $checkpointer;
    $this->sqlFormat = $sqlFormat;
  }

  function run() {
    foreach ($this->tasks as $task) {
      $cp = $this->checkpointer->readTask($task['id']);
      $this->processCheckpoint($task['id'], $cp);
    }

    $this->reportExceptions('Skipped tasks', $this->skippedTasks);
    $this->reportExceptions('Unhandled tasks', $this->unhandledTasks);
    $this->reportExceptions('Unchanged time limits', $this->unchangedTasks);
  }

  private function processCheckpoint(string $taskId, ?TaskCheckpoint $cp) {
    if (!$cp) {
      $this->unhandledTasks[] = $taskId;
    } else if ($cp->skipped) {
      $this->skippedTasks[] = $taskId;
    } else if ($cp->acceptedTimeLimit) {
      $params = $this->db->getTaskParams($taskId);
      $timeLimit = $params['timelimit'];
      if ($cp->acceptedTimeLimit == $timeLimit) {
        $this->unchangedTasks[] = $taskId;
      } else {
        $this->reportChange($taskId, $timeLimit, $cp->acceptedTimeLimit);
      }
    } else {
      $this->unhandledTasks[] = $taskId;
    }
  }

  private function reportChange($taskId, $oldTimeLimit, $newTimeLimit) {
    Log::info("Time limit for %s changed from %g to %g",
              [ $taskId, $oldTimeLimit, $newTimeLimit ]);
  }

  private function reportExceptions(string $description, array $taskIds) {
    $cnt = count($taskIds);
    if ($cnt) {
      $joined = implode(', ', $taskIds);
      $msg = "{$description} ({$cnt}): {$joined}";
      Log::info($msg);
    }
  }
}
