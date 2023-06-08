<?php

/**
 * Keeps track of the current task, job and test. Also wraps getters around
 * some array fields.
 **/

class WorkStack {
  private static int $taskCount;
  private static array $task;
  private static array $taskParams;
  private static int $taskNo;

  private static int $jobCount;
  private static array $job;
  private static string $jobOwner;
  private static int $jobNo;

  private static int $testCount;
  private static array $test;
  private static int $testNo;

  static function setTaskCount(int $taskCount): void {
    self::$taskCount = $taskCount;
    self::$taskNo = 0;
  }

  static function getTaskCount(): int {
    return self::$taskCount;
  }

  static function setTask(array $task, array $taskParams): void {
    self::$task = $task;
    self::$taskParams = $taskParams;
    self::$taskNo++;
  }

  static function getTask(): array {
    return self::$task;
  }

  static function getTaskParams(): array {
    return self::$taskParams;
  }

  static function getTaskTimeLimit(): float {
    return (float)self::$taskParams['timelimit'];
  }

  static function getTaskTestCount(): int {
    return self::$task['test_count'];
  }

  static function getTaskNo(): int {
    return self::$taskNo;
  }

  static function setJobCount(int $jobCount): void {
    self::$jobCount = $jobCount;
    self::$jobNo = 0;
  }

  static function getJobCount(): int {
    return self::$jobCount;
  }

  static function setJob(array $job, string $jobOwner): void {
    self::$job = $job;
    self::$jobOwner = $jobOwner;
    self::$jobNo++;
  }

  static function getJob(): array {
    return self::$job;
  }

  static function getJobOwner(): string {
    return self::$jobOwner;
  }

  static function getJobNo(): int {
    return self::$jobNo;
  }

  static function setTestCount(int $testCount): void {
    self::$testCount = $testCount;
    self::$testNo = 0;
  }

  static function getTestCount(): int {
    return self::$testCount;
  }

  static function setTest(array $test): void {
    self::$test = $test;
    self::$testNo++;
  }

  static function getTest(): array {
    return self::$test;
  }

  static function getTestOldMessage(): string {
    return self::$test['grader_message'];
  }

  static function getTestOldPoints(): int {
    return self::$test['points'];
  }

  static function getTestOldTime(): float {
    return (float)self::$test['exec_time'] / 1000;
  }

  static function getTestOldTle(): bool {
    return self::getTestOldTime() >= self::getTaskTimeLimit();
  }

  static function getTestNo(): int {
    return self::$testNo;
  }
}
