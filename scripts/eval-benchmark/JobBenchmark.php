<?php

class JobBenchmark {
  private Database $db;
  private array $job;
  private string $owner;
  private array $tests;
  private int $numTaskTests;
  private int $numJobTests;
  private ClassicGrader $grader;
  private array $results = [];

  function __construct(array& $job, Database $db) {
    $this->job = $job;
    $this->db = $db;
    $this->owner = $this->db->getUser($this->job['user_id']);
    WorkStack::setJob($this->job, $this->owner);
    $this->grader = new BenchmarkGrader(
      WorkStack::getTask(), WorkStack::getTaskParams(), $this->job);
  }

  function run(): void {
    Log::default('Benchmarking job %d/%d (ID #%d, user %s).',
                 [ WorkStack::getJobNo(), WorkStack::getJobCount(),
                   $this->job['id'], $this->owner ]);

    $this->tests = $this->db->loadTests($this->job['id']);
    $this->numJobTests = count($this->tests);
    $this->numTaskTests = WorkStack::getTaskTestCount();

    if ($this->numJobTests != $this->numTaskTests) {
      $this->reportBadTestCount();
    } else {
      $this->benchmarkAllTests();
    }
  }

  private function reportBadTestCount(): void {
    Log::warn('SKIPPING (task specifies %d tests, job has %d)',
              [ $this->numTaskTests, $this->numJobTests],
              1);
  }

  private function benchmarkAllTests(): void {
    WorkStack::setTestCount($this->numJobTests);

    if ($this->compileJobSource()) {
      Log::default('Running %d tests', [ $this->numJobTests ], 1);
      foreach ($this->tests as $test) {
        $tb = new TestBenchmark($test, $this->grader, $this->db);
        $tb->run();
      }
    }
  }

  private function compileJobSource(): bool {
    try {
      $this->grader->compileJobSource();
      return true;
    } catch (EvalUserCompileError $e) {
      Log::warn('Compilation error.', [], 2);
      return false;
    }
  }

  function getResults(): array {
    return $this->results;
  }

}
