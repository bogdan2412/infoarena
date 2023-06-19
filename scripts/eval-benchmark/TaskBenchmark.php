<?php

class TaskBenchmark {
  const METHOD_FEWEST_MISTAKES = 0;
  const METHOD_FEWEST_MISTAKES_ROUNDED = 1;
  const METHOD_LARGEST_TIMES = 2;
  const METHOD_LARGEST_TIMES_ROUNDED = 3;

  private Checkpointer $checkpointer;
  private Database $db;
  private array $task;
  private array $params;
  private array $jobs;
  private array $adminJobs;
  private TimeAnalyzer $timeAnalyzer;
  private array $newLimits;
  private float $prevCustomLimit = 0.0;
  private bool $choiceMade = false;

  function __construct(array $task, Database $db, Checkpointer $checkpointer) {
    $this->task = $task;
    $this->db = $db;
    $this->checkpointer = $checkpointer;

    $this->params = $db->getTaskParams($task['id']);
    WorkStack::setTask($this->task, $this->params);
  }

  function run() {
    $this->printHeader();
    $this->loadJobs();

    $choice = $this->getJobChoice();
    $this->actOnJobChoice($choice);
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

  private function loadJobs() {
    $this->jobs = $this->db->loadJobs($this->task['id']);
    $this->adminJobs = $this->db->filterAdminJobs($this->jobs);
  }

  private function getJobChoice(): string {
    $countAdmin = count($this->adminJobs);
    $countAll = count($this->jobs);
    return Choice::selectFrom([
      'a' => sprintf('benchmark admin jobs only (%d)', $countAdmin),
      'e' => sprintf('benchmark every job (%d)', $countAll),
    ]);
  }

  private function actOnJobChoice(string $choice): void {
    switch ($choice) {
      case 'a':
        $this->benchmarkJobs($this->adminJobs);
        break;

      case 'e':
        $this->benchmarkJobs($this->jobs);
        break;
    }
  }

  private function benchmarkJobs(array& $jobs): void {
    WorkStack::setJobCount(count($jobs));
    $timePairs = [];

    foreach ($jobs as $job) {
      $jb = new JobBenchmark($job, $this->db);
      $jobTimePairs = $jb->run();
      array_push($timePairs, ...$jobTimePairs);
    }

    $this->recommendNewTimeLimit($timePairs);
  }

  private function recommendNewTimeLimit(array& $timePairs): void {
    $this->timeAnalyzer = new TimeAnalyzer($timePairs);
    if ($this->timeAnalyzer->isCornerCase()) {
      return;
    }

    $this->makeNewLimits();
    $this->logRecommendations();

    do {
      $choice = $this->getTimeChoice();
      $this->actOnTimeChoice($choice);
    } while (!$this->choiceMade);
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

  private function getTimeChoice(): string {
    $choices = [];
    $fmt = 'accept recommendation based on %s (%g s)';
    foreach ($this->newLimits as $method => $time) {
      $desc = $this->getMethodDescription($method);
      $choices['1' + $method] = sprintf($fmt, $desc, $time);
    }
    if ($this->prevCustomLimit) {
      $msg = sprintf('accept the proposed custom time limit (%g s)', $this->prevCustomLimit);
      $choices['5'] = $msg;
    }
    $choices['c'] = 'propose a custom time limit';
    return Choice::selectFrom($choices);
  }

  private function actOnTimeChoice(string $choice): void {
    switch ($choice) {
      case '1':
      case '2':
      case '3':
      case '4':
        $this->choiceMade = true;
        break;

      case '5':
        $this->choiceMade = true;
        break;

      case 'c':
        $this->readAndReportCustomLimit();
        break;
    }
  }

  private function readAndReportCustomLimit() {
    do {
      $limit = (float)readline('Please enter a custom time limit: ');
    } while (($limit <= 0) || ($limit > WorkStack::getTaskTimeLimit()));

    $this->prevCustomLimit = $limit;
    $mistakes = $this->timeAnalyzer->countMistakes($limit);
    Log::info('A limit of %g s leads to %d mistakes.', [ $limit, $mistakes ]);
  }
}
