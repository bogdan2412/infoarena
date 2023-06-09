<?php

class TestBenchmark {
  private array $test;
  private NewResult $result;
  private ClassicGrader $grader;
  private Database $db;

  function __construct(array& $test, ClassicGrader $grader, Database $db) {
    $this->test = $test;
    $this->grader = $grader;
    $this->db = $db;
    WorkStack::setTest($test);
  }

  function run(): ?TimePair {
    $action = TestAction::recommend();

    if ($action == TestAction::ACTION_USE) {
      return $this->executeTest();
    } else {
      $this->reportUnusableTest($action);
      return null;
    }
  }

  private function executeTest(): ?TimePair {
    $this->result = $this->grader->runTest();
    if ($this->result->status == NewResult::ST_OTHER) {
      $this->reportIgnoredAfterRun();
      return null;
    } else {
      $timeInfo = $this->assembleTimePair();
      return $timeInfo;
    }
  }

  private function reportIgnoredAfterRun(): void {
    $fmt = 'Test #%02d: ignored after grading ' .
      '(old points: %d, old time: %g, old message: %s) ' .
      '(new time: %d, new message: %s)';

    $args = [
      WorkStack::getTestNo(),
      WorkStack::getTestOldPoints(),
      WorkStack::getTestOldTime(),
      WorkStack::getTestOldMessage(),
      $this->result->time,
      $this->result->message,
    ];

    Log::warn($fmt, $args, 2);
  }

  private function assembleTimePair(): TimePair {
    $oldTime = WorkStack::getTestOldTime();
    $oldTle = WorkStack::getTestOldTle();
    $newTime = $this->result->time;
    $newTle = ($this->result->status == NewResult::ST_TLE);

    $fmt = 'Test #%02d: old time %g%s, new time %g%s';
    $args = [
      WorkStack::getTestNo(),
      $oldTime,
      $oldTle ? ' (TLE)' : '',
      $newTime,
      $newTle ? ' (TLE)' : '',
    ];

    Log::default($fmt, $args, 2);

    return new TimePair($oldTime, $oldTle, $newTime, $newTle);
  }

  private function reportUnusableTest(int $action): void {
    $verdict = TestAction::getVerdict($action);
    $fmt = 'Test #%02d: %s (points: %d, time: %g, grader message: %s)';
    $args = [
      WorkStack::getTestNo(),
      $verdict,
      WorkStack::getTestOldPoints(),
      WorkStack::getTestOldTime(),
      WorkStack::getTestOldMessage(),
    ];

    if ($action == TestAction::ACTION_REPORT) {
      Log::warn($fmt, $args, 2);
    } else {
      Log::info($fmt, $args, 2);
    }
  }

}
