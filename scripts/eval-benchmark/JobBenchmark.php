<?php

class JobBenchmark {
  const GOOD_LANGUAGES = [ 'c', 'c-32', 'cpp', 'cpp-32' ];

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

  // Returns an array of TimePair's
  function run(): array {
    Log::default('Benchmarking job %d/%d (ID #%d, user %s).',
                 [ WorkStack::getJobNo(), WorkStack::getJobCount(),
                   $this->job['id'], $this->owner ]);

    $this->tests = $this->db->loadTests($this->job['id']);
    $this->numJobTests = count($this->tests);
    $this->numTaskTests = WorkStack::getTaskTestCount();

    if ($this->sanityCheck()) {
      return $this->benchmarkAllTests();
    } else {
      return [];
    }
  }

  private function sanityCheck(): bool {
    if ($this->numJobTests != $this->numTaskTests) {
      $this->reportBadTestCount();
      return false;
    }
    $lang = $this->job['compiler_id'];
    if (!in_array($lang, self::GOOD_LANGUAGES)) {
      Log::warn('SKIPPING: not handling %s code', [ $lang ], 1);
      return false;
    }

    return true;
  }

  private function reportBadTestCount(): void {
    Log::warn('SKIPPING (task specifies %d tests, job has %d)',
              [ $this->numTaskTests, $this->numJobTests ],
              1);
  }

  private function benchmarkAllTests(): array {
    WorkStack::setTestCount($this->numJobTests);
    $result = [];

    if ($this->compileJobSource()) {
      Log::default('Running %d tests', [ $this->numJobTests ], 1);
      foreach ($this->tests as $test) {
        $tb = new TestBenchmark($test, $this->grader, $this->db);
        $timePair = $tb->run(); // possibly null
        if ($timePair) {
          $result[] = $timePair;
        }
      }
    }

    return $result;
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
