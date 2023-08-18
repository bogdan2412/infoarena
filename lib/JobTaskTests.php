<?php

class JobTaskTests extends TaskTests {

  private Job $job;
  private array $jobTests;
  private array $groupScores;
  private array $solvedGroups;

  function __construct(Job $job, Task $task) {
    parent::__construct($task);
    $this->job = $job;
    $this->collectJobTests();
    $this->computeScores();
  }

  private function collectJobTests(): void {
    $jobTests = JobTest::getAllForJob($this->job->id);
    $this->jobTests = [];
    foreach ($jobTests as $jt) {
      $this->jobTests[$jt->test_number] = $jt;
    }
  }

  private function computeScores(): void {
    foreach ($this->groups as $id => $ignored) {
      $this->groupScores[$id] = 0;
      $this->solvedGroups[$id] = true;
    }

    foreach ($this->jobTests as $test) {
      $this->sanityCheck($test);
      $this->processTest($test);
    }

    foreach ($this->groups as $id => $ignored) {
      if (!$this->solvedGroups[$id]) {
        $this->groupScores[$id] = 0;
      }
    }
  }

  private function sanityCheck(JobTest $test): void {
    $number = $test->test_number;
    if ($number < 1 || $number > $this->numTests) {
      $msg = sprintf('Testul %d are un număr incorect (problema are %d teste).',
                     $number, $this->numTests);
      throw new JobTestException($msg);
    }

    $group = $test->test_group;
    $stmtGroup = $this->testGroup[$number];
    if ($group != $stmtGroup) {
      $msg = sprintf('Testul %d figurează în grupul %d, problema îl pune în grupul %d.',
                     $number, $group, $stmtGroup);
      throw new JobTestException($msg);
    }
  }

  private function processTest(JobTest $test): void {
    $g = $test->test_group;
    $this->groupScores[$g] += $test->points;
    $this->solvedGroups[$g] &= ($test->points > 0);
  }

  function getGroupScore(int $groupNo): int {
    return $this->groupScores[$groupNo];
  }

  function isTestViewable(int $testNo): bool {
    return
      $this->job->isScoreViewable() ||
      ($this->job->isPartialFeedbackViewable() && $this->isPublicTest($testNo));
  }

  function getJobTest(int $testNo): JobTest {
    return $this->jobTests[$testNo];
  }

  function hasJobTests(): bool {
    return count($this->jobTests) > 0;
  }

}
