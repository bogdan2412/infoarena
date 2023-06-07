<?php

class JobBenchmark {
  private Database $db;
  private array $task;
  private float $timeLimit;
  private array $jobs;

  function __construct(array $task, float $timeLimit, array& $jobs, Database $db) {
    $this->task = $task;
    $this->timeLimit = $timeLimit;
    $this->db = $db;
    $this->jobs = $jobs;
  }

  function run() {
  }

}
