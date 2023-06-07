<?php

class TaskBenchmark {
  private Checkpointer $checkpointer;
  private Database $db;
  private string $taskId;
  private array $tasks;

  function __construct(Checkpointer $checkpointer, Database $db, string $taskId) {
    $this->checkpointer = $checkpointer;
    $this->db = $db;
    $this->taskId = $taskId;
  }

  function run() {
    $this->db->loadAdmins();
    $this->loadTasks();
    foreach ($this->tasks as $ord => $task) {
      $this->benchmarkTask($task, 1 + $ord);
    }
  }

  function loadTasks() {
    $this->tasks = ($this->taskId)
      ? [ $this->db->loadTaskById($this->taskId) ]
      : $this->db->loadTasks();
  }

  function benchmarkTask(array $task, int $ord) {
    $timeLimit = $this->db->getTaskTimeLimit($task['id']);
    $this->printTaskHeader($task, $ord, $timeLimit);

    $jobs = $this->db->loadJobs($task['id']);
    $adminJobs = $this->db->filterAdminJobs($jobs);

    $choice = Choice::selectFrom([
      'a' => sprintf('benchmark admin jobs only (%d)', count($adminJobs)),
      'e' => sprintf('benchmark every job (%d)', count($jobs)),
    ]);

    $jobsToBenchmark = ($choice == 'a') ? $adminJobs : $jobs;
    $jb = new JobBenchmark($task, $timeLimit, $jobsToBenchmark, $this->db);
    $jb->run();
  }

  function printTaskHeader(array& $task, int $ord, float $timeLimit) {
    $total = count($this->tasks);

    $header = sprintf('| %s (task %d/%d, %d tests, %g s) |',
                      $task['id'],
                      $ord,
                      $total,
                      $task['test_count'],
                      $timeLimit);
    $len = mb_strlen($header);
    $line = '+' . str_repeat('-', $len - 2) . '+';

    Log::emptyLine();
    Log::info($line);
    Log::info($header);
    Log::info($line);
    Log::emptyLine();
  }

}
