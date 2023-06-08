<?php

class TestBenchmark {
  private Database $db;
  private array $test;

  function __construct(array& $test, Database $db) {
    $this->db = $db;
    $this->test = $test;
  }

  function run() {
  }
}
