<?php

class JobBenchmark {
  private Database $db;
  private array $task;
  private float $timeLimit;
  private array $job;

  function __construct(array& $job, array $task, float $timeLimit, Database $db) {
    $this->task = $task;
    $this->timeLimit = $timeLimit;
    $this->db = $db;
    $this->job = $job;
  }

  function run() {
  }

}
