<?php

class TimeAnalyzer {
  // Don't recommend anything if the time limit is already this low.
  // If we do recommend something, don't go below this limit.
  const MIN_TIME_LIMIT = 0.0;

  // Time limit recommendations below this limit will be rounded up to a
  // multiple of 0.05. Time limit recommendations above this limit will be
  // rounded up to a multiple of 0.1.
  const ROUND_THRESHOLD = 0.5;
  const SMALL_QUANTUM = 0.05;
  const LARGE_QUANTUM = 0.1;

  private array $timePairs;
  private int $n;
  private float $oldLimit;
  private int $oldPassing;
  private int $oldTle;
  private float $maxOldPassingTime;
  private float $maxNewPassingTime; // from among OLD tests that passed

  function __construct(array $timePairs) {
    $this->timePairs = $timePairs;
    $this->n = count($this->timePairs); // syntactic sugar
    $this->oldLimit = WorkStack::getTaskTimeLimit();
    $this->sortPairsByNewTime();
    $this->computeStatistics();
    $this->logStatistics();
  }

  function round(float $time): float {
    $time = max($time, self::MIN_TIME_LIMIT);
    $quantum = ($time < self::ROUND_THRESHOLD)
      ? self::SMALL_QUANTUM
      : self::LARGE_QUANTUM;

    $rounded = round($time / $quantum) * $quantum;
    return $rounded;
  }

  function recommendByLargestPassingTimes(): float {
    if ($this->isCornerCase()) {
      return 0.0;
    }
    $factor = $this->maxNewPassingTime / $this->maxOldPassingTime;
    return $this->oldLimit * $factor;
  }

  // Returns 0 when we cannot make a recommendation.
  function recommendByFewestMistakes(): float {
    if ($this->isCornerCase()) {
      return 0.0;
    }
    return $this->iterateNewTimes();
  }

  function isCornerCase(): bool {
    if (!$this->n) {
      Log::warn('No recommendation: no tests were run.');
      return true;
    } else if ($this->oldLimit <= self::MIN_TIME_LIMIT) {
      Log::warn('No recommendation: time limit is already small.');
      return true;
    } else if (!$this->maxOldPassingTime) {
      Log::warn('No recommendation: all tests passed in 0 time.');
      return true;
    }

    return false;
  }

  private function sortPairsByNewTime(): void {
    usort($this->timePairs, function(TimePair $a, TimePair $b) {
      return $a->newTime <=> $b->newTime;
    });

    // foreach ($this->timePairs as $i => $tp) {
    //   printf("%3d %.03f %d %.03f %d\n", $i + 1, $tp->oldTime, $tp->oldTle, $tp->newTime, $tp->newTle);
    // }
  }

  private function computeStatistics(): void {
    $this->oldPassing = 0;
    $this->maxOldPassingTime = 0.0;
    $this->maxNewPassingTime = 0.0;
    foreach ($this->timePairs as $tp) {
      if (!$tp->oldTle) {
        $this->oldPassing++;
        $this->maxOldPassingTime = max($this->maxOldPassingTime, $tp->oldTime);
        $this->maxNewPassingTime = max($this->maxNewPassingTime, $tp->newTime);
      }
    }
    $this->oldTle = $this->n - $this->oldPassing;
  }

  private function logStatistics(): void {
    Log::info('Ran %d tests (%d passing, %d failing with TLE).',
              [ $this->n, $this->oldPassing, $this->oldTle ]);
    Log::info('Maximum passing times: %g old, %g new.',
              [ $this->maxOldPassingTime, $this->maxNewPassingTime ]);
  }

  private function iterateNewTimes(): float {
    // Start with a new limit of 0, which means that no tests pass.
    // Then every test that used to pass represents a mistake.
    $mistakes = $this->oldPassing;
    $minMistakes = $mistakes;
    $minPassing = 0;

    for ($k = 0; $k < $this->n; $k++) {
      // What happens if we increased the time enough to allow test $k to pass?
      if ($this->timePairs[$k]->oldTle) {
        $mistakes++;
      } else {
        $mistakes--;
      }

      if ($mistakes <= $minMistakes) {
        $minMistakes = $mistakes;
        $minPassing = $k + 1;
      }
    }

    return $this->getAverageLimit($minPassing);
  }

  private function getAverageLimit(int $k): float {
    if ($k == 0) {
      return $this->timePairs[0]->newTime * 0.9;
    } else if ($k == $this->n) {
      return $this->timePairs[$k - 1]->newTime * 1.1;
    } else {
      $me = $this->timePairs[$k]->newTime;
      $prev = $this->timePairs[$k - 1]->newTime;
      return ($me + $prev) / 2;
    }
  }

  function countMistakes($time): int {
    $result = 0;
    foreach ($this->timePairs as $tp) {
      $newTle = $tp->newTle || ($tp->newTime > $time);
      if ($tp->oldTle ^ $newTle) {
        $result++;
      }
    }
    return $result;
  }
}
