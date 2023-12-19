<?php

class Mistakes {
  public int $newPasses;
  public int $newTles;

  function __construct() {
    $this->newPasses = 0;
    $this->newTles = 0;
  }

  function getTotal(): int {
    return $this->newPasses + $this->newTles;
  }
}
