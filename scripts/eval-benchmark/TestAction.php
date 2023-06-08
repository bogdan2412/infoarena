<?php

class TestAction {
  // Things we can do with a test case
  const ACTION_IGNORE = 0;  // ignore it
  const ACTION_REPORT = 1;  // report it as inconsistent
  const ACTION_USE = 2;     // use it for benchmarking

  // Due to graders, success messages can be quite baroque.
  // Any tests that scored >= 1 points are assumed to be successful.
  // Partial scores are fine -- we only care about the time limit.
  //
  // The messages below indicate a TLE status.
  const TLE_MESSAGES = [
    'Time limit exceeded',
    'Time limit exceeded.',
    'Wall time limit exceeded',
    'Wall time limit exceeded.',
  ];

  /**
   * Figure out what to do with the current test based on its outcome.
   **/
  static function recommend(): int {
    $timeLimit = WorkStack::getTaskTimeLimit();
    $time = WorkStack::getTestOldTime();
    $points = WorkStack::getTestOldPoints();
    $message = WorkStack::getTestOldMessage();

    $isInTime = $time < $timeLimit;
    $hasTleMsg = in_array($message, self::TLE_MESSAGES);

    return self::discern($isInTime, $hasTleMsg, $points);
  }

  private static function discern(bool $isInTime, bool $hasTleMsg, int $points): int {
    // 8 cases arise.
    if (($isInTime == $hasTleMsg) ||
        ($points && !$isInTime)) {
      // Cases 1-4: The TLE message (or its absence) is inconsistent
      // with the test running in time (or not).
      // Case 5: The test did not run in time yet still got some points.
      return self::ACTION_REPORT;
    } else if (!$points && $isInTime) {
      // Case 6: Do nothing. Test got 0 points due to other errors:
      // wrong answer, memory limit exceeded etc.
      return self::ACTION_IGNORE;
    } else {
      // Cases 7-8: TLE or the test got some points.
      return self::ACTION_USE;
    }
  }

  static function getVerdict($action): string {
    return ($action == self::ACTION_REPORT)
      ? 'inconsistent'
      : 'ignored';
  }

}
