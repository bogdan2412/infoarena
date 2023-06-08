<?php

class JobBenchmark {
  private Database $db;
  private array $job;
  private string $owner;
  private array $tests;
  private int $numTaskTests;
  private int $numJobTests;

  function __construct(array& $job, Database $db) {
    $this->job = $job;
    $this->db = $db;
    $this->owner = $this->db->getUser($this->job['user_id']);
    WorkStack::setJob($this->job, $this->owner);
  }

  function run() {
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

  private function reportBadTestCount() {
    Log::warn('SKIPPING (task specifies %d tests, job has %d)',
              [ $this->numTaskTests, $this->numJobTests],
              1);
  }

  private function benchmarkAllTests() {
    WorkStack::setTestCount($this->numJobTests);
    Log::default('Running %d tests', [ $this->numJobTests ], 1);
    foreach ($this->tests as $test) {
      $tb = new TestBenchmark($test, $this->db);
    }
  }

}
