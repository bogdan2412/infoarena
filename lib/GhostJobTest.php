<?php

/**
 * A JobTest placeholder to be used when a task is edited, and more tests are
 * added, after a job is submitted.
 *
 * Implemented as a singleton.
 **/

class GhostJobTest extends JobTest {
  private static $instance = null;

  public string $grader_message = 'N/A';
  public string $points = 'N/A';

  static function getInstance(): GhostJobTest {
    if (!self::$instance) {
      self::$instance = new static();
    }
    return self::$instance;
  }

  function getTimeUsedMessage(): string {
    return 'N/A';
  }

  function getMemoryUsedMessage(): string {
    return 'N/A';
  }
}
