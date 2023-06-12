<?php

class TaskBenchmark {
  const METHOD_FEWEST_MISTAKES = 0;
  const METHOD_FEWEST_MISTAKES_ROUNDED = 1;
  const METHOD_LARGEST_TIMES = 2;
  const METHOD_LARGEST_TIMES_ROUNDED = 3;

  private Checkpointer $checkpointer;
  private Database $db;
  private array $task;
  private bool $batchMode;
  private int $numJobs, $numAdminJobs;
  private array $jobs;
  private TimeAnalyzer $timeAnalyzer;
  private array $newLimits;
  private float $prevCustomLimit = 0.0;

  private TaskCheckpoint $cp;

  function __construct(array $task, Database $db, Checkpointer $checkpointer, bool $batchMode) {
    $this->task = $task;
    $this->db = $db;
    $this->checkpointer = $checkpointer;
    $this->batchMode = $batchMode;

    $this->cp = new TaskCheckpoint();

    $params = $db->getTaskParams($task['id']);
    WorkStack::setTask($this->task, $params);
  }

  function getTask(): array {
    return $this->task;
  }

  function getCheckpoint(): TaskCheckpoint {
    return $this->cp;
  }

  function run() {
    $this->printHeader();
    $this->loadCheckpoint();
    $this->countJobs();

    if ($this->batchMode) {
      if ($this->needsFullBenchmark()) {
        $this->actionBenchmarkAllJobs();
      }
    } else {
      $this->interactiveLoop();
    }
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

  private function loadCheckpoint(): void {
    $cp = $this->checkpointer->readTask($this->task['id']);
    if ($cp != null) {
      $this->cp = $cp;
      $this->logCheckpointInfo();
    }
  }

  private function logCheckpointInfo(): void {
    $ops = [];
    if ($this->cp->benchmarked) {
      $ops[] = sprintf('benchmarked %d testcases', count($this->cp->timePairs));
    }
    if ($this->cp->acceptedTimeLimit) {
      $ops[] = sprintf('accepted a new time limit of %g s', $this->cp->acceptedTimeLimit);
    }
    if ($this->cp->skipped) {
      $ops[] = 'skipped permanently';
    }

    $msg = sprintf('Found a checkpoint with the following operations: %s.',
                   join(', ', $ops));
    Log::warn($msg);
  }

  private function countJobs(): void {
    $this->numJobs = $this->db->countJobs($this->task['id']);
    $this->numAdminJobs = $this->db->countAdminJobs($this->task['id']);
  }

  private function needsFullBenchmark(): bool {
    return
      !$this->cp->skipped &&
      !$this->cp->benchmarked;
  }

  private function interactiveLoop(): void {
    while (!$this->cp->skipped && !$this->cp->acceptedTimeLimit) {
      $this->maybeComputeRecommendations();
      $action = $this->chooseAction();
      $this->$action();
    }
  }

  private function maybeComputeRecommendations(): void {
    if (!empty($this->cp->timePairs) &&
        empty($this->newLimits)) {
      $this->timeAnalyzer = new TimeAnalyzer($this->cp->timePairs);

      if ($this->timeAnalyzer->isCornerCase()) {
        return;
      }

      $this->makeNewLimits();
      $this->logRecommendations();
    }
  }

  private function makeNewLimits(): void {
    $ta = $this->timeAnalyzer; // syntactic sugar
    $limitMistakes = $ta->recommendByFewestMistakes();
    $limitLargestTimes = $ta->recommendByLargestPassingTimes();

    $this->newLimits = [
      self::METHOD_FEWEST_MISTAKES => $limitMistakes,
      self::METHOD_FEWEST_MISTAKES_ROUNDED => $ta->round($limitMistakes),
      self::METHOD_LARGEST_TIMES => $limitLargestTimes,
      self::METHOD_LARGEST_TIMES_ROUNDED => $ta->round($limitLargestTimes),
    ];
  }

  private function logRecommendations() {
    Log::success('New time limit recommendations:');
    $fmt = '* Based on %s: %g s (%d mistakes).';
    foreach ($this->newLimits as $method => $time) {
      $desc = $this->getMethodDescription($method);
      $mistakes = $this->timeAnalyzer->countMistakes($time);
      Log::success($fmt, [ $desc, $time, $mistakes ]);
    }
  }

  private function getMethodDescription(int $method): string {
    switch ($method) {
      case self::METHOD_FEWEST_MISTAKES: return 'fewest mistakes';
      case self::METHOD_FEWEST_MISTAKES_ROUNDED: return 'fewest mistakes, rounded';
      case self::METHOD_LARGEST_TIMES: return 'largest passing times';
      case self::METHOD_LARGEST_TIMES_ROUNDED: return 'largest passing times, rounded';
    }
  }

  private function chooseAction(): string {
    $choices = $this->collectChoices();
    $tcs = new TaskChoiceSelector();
    $tcs->addChoices($choices);
    $action = $tcs->chooseAction();
    return $action;
  }

  private function collectChoices(): array {
    $choices = ($this->cp->benchmarked)
      ? $this->makeTimeChoices()
      : $this->makeBenchmarkChoices();

    $common = $this->makeCommonChoices();
    $choices = array_merge($choices, $common);
    return $choices;
  }

  private function makeTimeChoices(): array {
    $choices = [];
    $fmt = 'accept recommendation based on %s (%g s)';
    foreach ($this->newLimits as $method => $time) {
      $desc = $this->getMethodDescription($method);
      $choices[] = new TaskChoice('1' + $method,
                                  sprintf($fmt, $desc, $time),
                                  'actionAcceptTimeLimit' . $method);
    }
    if ($this->prevCustomLimit) {
      $msg = sprintf('accept the proposed custom time limit (%g s)', $this->prevCustomLimit);
      $choices[] = new TaskChoice('5', $msg, 'actionAcceptCustomTimeLimit');
    }
    $choices[] = new TaskChoice('c', 'propose a custom time limit',
                                'actionProposeCustomTimeLimit');
    $choices[] = new TaskChoice('f', 'forget the current benchmarks',
                                'actionForgetBenchmarks');
    return $choices;
  }

  private function makeBenchmarkChoices(): array {
    return [
      new TaskChoice('b',
                     sprintf('benchmark %d admin jobs', $this->numAdminJobs),
                     'actionBenchmarkAdminJobs'),
      new TaskChoice('B',
                     sprintf('benchmark all %d jobs', $this->numJobs),
                     'actionBenchmarkAllJobs'),
    ];
  }

  private function makeCommonChoices(): array {
    return [
      new TaskChoice('s',
                     'skip this task temporarily (you will see it again next time)',
                     'actionSkipTemporarily'),
      new TaskChoice('S',
                     'skip this task permanently (you will not see it again)',
                     'actionSkipPermanently'),
    ];
  }

  private function actionBenchmarkAdminJobs() {
    $this->loadAdminJobs();
    $this->benchmarkJobs();
  }

  private function actionBenchmarkAllJobs() {
    $this->loadAllJobs();
    $this->benchmarkJobs();
  }

  private function loadAllJobs(): void {
    $this->jobs = $this->db->loadAllJobs($this->task['id']);
  }

  private function loadAdminJobs(): void {
    $this->jobs = $this->db->loadAdminJobs($this->task['id']);
  }

  private function benchmarkJobs(): void {
    WorkStack::setJobCount(count($this->jobs));
    $this->cp->timePairs = [];

    foreach ($this->jobs as $job) {
      $jb = new JobBenchmark($job, $this->db);
      $jobTimePairs = $jb->run();
      array_push($this->cp->timePairs, ...$jobTimePairs);
    }

    $this->cp->benchmarked = true;
    $this->save();
  }

  private function actionForgetBenchmarks() {
    $this->cp = new TaskCheckpoint();
    $this->jobs = [];
    $this->newLimits = [];
    $this->prevCustomLimit = 0.0;
  }

  private function actionProposeCustomTimeLimit() {
    do {
      $limit = (float)readline('Please enter a custom time limit: ');
    } while (($limit <= 0) || ($limit > WorkStack::getTaskTimeLimit()));

    $this->prevCustomLimit = $limit;
    $mistakes = $this->timeAnalyzer->countMistakes($limit);
    Log::info('A limit of %g s leads to %d mistakes.', [ $limit, $mistakes ]);
  }

  private function actionAcceptTimeLimit0() {
    $this->acceptTimeLimit($this->newLimits[self::METHOD_FEWEST_MISTAKES]);
  }

  private function actionAcceptTimeLimit1() {
    $this->acceptTimeLimit($this->newLimits[self::METHOD_FEWEST_MISTAKES_ROUNDED]);
  }

  private function actionAcceptTimeLimit2() {
    $this->acceptTimeLimit($this->newLimits[self::METHOD_LARGEST_TIMES]);
  }

  private function actionAcceptTimeLimit3() {
    $this->acceptTimeLimit($this->newLimits[self::METHOD_LARGEST_TIMES_ROUNDED]);
  }

  private function actionAcceptCustomTimeLimit() {
    $this->acceptTimeLimit($this->prevCustomLimit);
  }

  private function acceptTimeLimit(float $limit) {
    $this->cp->acceptedTimeLimit = $limit;
    $this->save();
  }

  private function actionSkipTemporarily() {
    $this->cp->skipped = true; // don't save
  }

  private function actionSkipPermanently() {
    $this->cp->skipped = true;
    $this->save();
  }

  private function save() {
    $this->checkpointer->writeTask($this);
  }
}
