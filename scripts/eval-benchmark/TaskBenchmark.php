<?php

class TaskBenchmark {
  private Checkpointer $checkpointer;
  private Database $db;
  private array $task;
  private array $params;

  private array $jobs;
  private array $adminJobs;

  function __construct(array $task, Database $db, Checkpointer $checkpointer) {
    $this->task = $task;
    $this->db = $db;
    $this->checkpointer = $checkpointer;

    $this->params = $db->getTaskParams($task['id']);
    WorkStack::setTask($this->task, $this->params);
  }

  function run() {
    $this->printHeader();
    $this->loadJobs();

    $choice = $this->getChoice();
    $this->actOnChoice($choice);
  }

  private function printHeader() {
    $header = sprintf('| %s (task %d/%d, %d tests, time limit %g s) |',
                      $this->task['id'],
                      WorkStack::getTaskNo(),
                      WorkStack::getTaskCount(),
                      $this->task['test_count'],
                      WorkStack::getTaskTimeLimit());
    $len = mb_strlen($header);
    $line = '+' . str_repeat('-', $len - 2) . '+';

    Log::emptyLine();
    Log::info($line);
    Log::info($header);
    Log::info($line);
    Log::emptyLine();
  }

  private function loadJobs() {
    $this->jobs = $this->db->loadJobs($this->task['id']);
    $this->adminJobs = $this->db->filterAdminJobs($this->jobs);
  }

  private function getChoice(): string {
    $countAdmin = count($this->adminJobs);
    $countAll = count($this->jobs);
    return Choice::selectFrom([
      'a' => sprintf('benchmark admin jobs only (%d)', $countAdmin),
      'e' => sprintf('benchmark every job (%d)', $countAll),
    ]);
  }

  private function actOnChoice(string $choice) {
    switch ($choice) {
      case 'a':
        $this->benchmarkJobs($this->adminJobs);
        break;

      case 'e':
        $this->benchmarkJobs($this->jobs);
        break;
    }
  }

  private function benchmarkJobs(array& $jobs) {
    WorkStack::setJobCount(count($jobs));
    foreach ($jobs as $job) {
      $jb = new JobBenchmark($job, $this->db);
      $jb->run();
    }
  }
}
