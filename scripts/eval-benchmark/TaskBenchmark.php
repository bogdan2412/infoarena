<?php

class TaskBenchmark {
  private Checkpointer $checkpointer;
  private Database $db;
  private array $task;
  private float $timeLimit;
  private int $ord;
  private int $numTasks;

  private array $jobs;
  private array $adminJobs;

  function __construct(Checkpointer $checkpointer, Database $db, array $task,
                       int $ord, int $numTasks) {
    $this->checkpointer = $checkpointer;
    $this->db = $db;
    $this->task = $task;
    $this->ord = $ord;
    $this->numTasks = $numTasks;
  }

  function run() {
    $this->timeLimit = $this->db->getTaskTimeLimit($this->task['id']);
    $this->printHeader();
    $this->loadJobs();

    $choice = $this->getChoice();
    $this->actOnChoice($choice);
  }

  function printHeader() {
    $header = sprintf('| %s (task %d/%d, %d tests, %g s) |',
                      $this->task['id'],
                      $this->ord,
                      $this->numTasks,
                      $this->task['test_count'],
                      $this->timeLimit);
    $len = mb_strlen($header);
    $line = '+' . str_repeat('-', $len - 2) . '+';

    Log::emptyLine();
    Log::info($line);
    Log::info($header);
    Log::info($line);
    Log::emptyLine();
  }

  function loadJobs() {
    $this->jobs = $this->db->loadJobs($this->task['id']);
    $this->adminJobs = $this->db->filterAdminJobs($this->jobs);
  }

  function getChoice(): string {
    $countAdmin = count($this->adminJobs);
    $countAll = count($this->jobs);
    return Choice::selectFrom([
      'a' => sprintf('benchmark admin jobs only (%d)', $countAdmin),
      'e' => sprintf('benchmark every job (%d)', $countAll),
    ]);
  }

  function actOnChoice(string $choice) {
    switch ($choice) {
      case 'a':
        $this->benchmarkJobs($this->adminJobs);
        break;

      case 'e':
        $this->benchmarkJobs($this->jobs);
        break;
    }
  }

  function benchmarkJobs(array& $jobs) {
    foreach ($jobs as $job) {
      $jb = new JobBenchmark($job, $this->task, $this->timeLimit, $this->db);
      $jb->run();
    }
  }
}
