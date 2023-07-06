<?php

class Reporter {
  private array $tasks;
  private Checkpointer $checkpointer;
  private bool $sqlFormat;

  private array $skippedTasks = [];
  private array $unhandledTasks = [];
  private array $unchangedTasks = [];

  function __construct(array $tasks, Checkpointer $checkpointer, bool $sqlFormat) {
    $this->tasks = $tasks;
    $this->checkpointer = $checkpointer;
    $this->sqlFormat = $sqlFormat;
  }

  function run() {
    foreach ($this->tasks as $task) {
      $cp = $this->checkpointer->readTask($task['id']);
      $this->processCheckpoint($task['id'], $task['params']['timelimit'], $cp);
    }

    $this->reportExceptions('Skipped tasks', $this->skippedTasks);
    $this->reportExceptions('Unhandled tasks', $this->unhandledTasks);
    $this->reportExceptions('Unchanged time limits', $this->unchangedTasks);
  }

  private function processCheckpoint(string $taskId, float $timeLimit, ?TaskCheckpoint $cp) {
    if (!$cp) {
      $this->unhandledTasks[] = $taskId;
    } else if ($cp->skipped) {
      $this->skippedTasks[] = $taskId;
    } else if ($cp->acceptedTimeLimit) {
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
