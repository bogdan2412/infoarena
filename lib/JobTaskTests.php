<?php

class JobTaskTests extends TaskTests {

  private Job $job;
  private array $jobTests;
  private array $groupScores;
  private array $solvedGroups;
  private array $staleTestNumbers = [];
  private array $staleTestGroups = [];

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
      if ($this->sanityCheck($test)) {
        $this->processTest($test);
      }
    }

    foreach ($this->groups as $id => $ignored) {
      if (!$this->solvedGroups[$id]) {
        $this->groupScores[$id] = 0;
      }
    }
  }

  private function sanityCheck(JobTest $test): bool {
    $number = $test->test_number;
    $group = $test->test_group;

    return
      $this->sanityCheckTestNumber($number) &&
      $this->sanityCheckTestGroup($number, $group);
  }

  private function sanityCheckTestNumber(int $number): bool {
    if ($number < 1 || $number > $this->numTests) {
      $this->staleTestNumbers[] = $number;
      return false;
    }

    return true;
  }

  private function sanityCheckTestGroup(int $number, int $group): bool {
    $stmtGroup = $this->testGroup[$number];
    if ($group != $stmtGroup) {
      $this->staleTestGroups[$number] = $group;
      return false;
    }

    return true;
  }

  private function processTest(JobTest $test): void {
    $g = $test->test_group;
    $this->groupScores[$g] += $test->points;
    $this->solvedGroups[$g] &= ($test->points > 0);
  }

  function getErrors(): array {
    $result = [];

    if (count($this->staleTestNumbers)) {
      $joined = implode(', ', $this->staleTestNumbers);
      $result[] = sprintf('Testele %s au numere incorecte (problema are %d teste).',
                          $joined, $this->numTests);
    }

    foreach ($this->staleTestGroups as $number => $oldGroup) {
      $result[] = sprintf(
        'Testul %d figurează în grupul %d, problema îl pune în grupul %d.',
        $number, $oldGroup, $this->testGroup[$number]);
    }

    for ($i = 1; $i <= $this->numTests; $i++) {
      if (!isset($this->jobTests[$i])) {
        $result[] = sprintf('Testul %d nu există.', $i);
      }
    }

    return $result;
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
    return $this->jobTests[$testNo]
      ?? GhostJobTest::getInstance();
  }

  function hasJobTests(): bool {
    return count($this->jobTests) > 0;
  }

}
