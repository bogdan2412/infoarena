<?php

class TestResult {
  const ST_OK = 0;    // Test ran in time. The output may or may not be correct.
  const ST_TLE = 1;   // Test exceeded the time limit.
  const ST_OTHER = 2; // Other failures -- killed, memory limit exceeded, etc.

  public int $status;
  public float $time;
  public string $message;

  function __construct(int $status, float $time, string $message) {
    $this->status = $status;
    $this->time = $time;
    $this->message = $message;
  }
}
