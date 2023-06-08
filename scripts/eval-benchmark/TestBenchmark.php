<?php

class TestBenchmark {
  private array $test;
  private TestResult $result;
  private ClassicGrader $grader;
  private Database $db;

  function __construct(array& $test, ClassicGrader $grader, Database $db) {
    $this->test = $test;
    $this->grader = $grader;
    $this->db = $db;
    WorkStack::setTest($test);
  }

  function run(): ?TimeInfo {
    $timeLimit = WorkStack::getTaskTimeLimit();
    $action = TestAction::recommend($this->test, $timeLimit);

    if ($action == TestAction::ACTION_USE) {
      return $this->executeTest();
    } else {
      $this->reportUnusableTest();
      return null;
    }
  }

  private function executeTest(): ?TimeInfo {
    $this->result = $this->grader->runTest($this->test);
    if ($this->result->status == TestResult::ST_OTHER) {
      $this->reportIgnoredAfterRun();
      return null;
    } else {
      $timeInfo = $this->assembleTimeInfo();
      return $timeInfo;
    }
  }

  private function reportIgnoredAfterRun() {
    $fmt = 'Test #%02d: ignored after grading ' .
      '(old points: %d, old time: %g, old message: %s) ' .
      '(new time: %d, new message: %s)';

    $args = [
      $this->test['test_number'],
      $this->test['points'],
      $this->test['exec_time'],
      $this->test['grader_message'],
      $this->result->time,
      $this->result->message,
    ];

    Log::warn($fmt, $args, 2);
  }

  private function assembleTimeInfo(): TimeInfo {
    $timeLimit = WorkStack::getTaskTimeLimit();
    $oldTime = $this->test['exec_time'] / 1000;
    $oldTle = ($oldTime >= $timeLimit);
    $newTime = $this->result->time;
    $newTle = ($this->result->status == TestResult::ST_TLE);

    $fmt = 'Test #%02d: old time %g%s, new time %g%s';
    $args = [
        $this->test['test_number'],
        $oldTime,
        $oldTle ? ' (TLE)' : '',
        $newTime,
        $newTle ? ' (TLE)' : '',
    ];

    Log::default($fmt, $args, 2);

    return new TimeInfo($oldTime, $oldTle, $newTime, $newTle);
  }

}
