<?php

class JobPenalty {
  public int $amount;
  public string $description;

  function __construct(int $amount, string $description) {
    $this->amount = $amount;
    $this->description = $description;
  }

  function add(JobPenalty $other): JobPenalty {
    $description = ($this->description && $other->description)
      ? ($this->description . ' + ' . $other->description)
      : ($this->description . $other->description);
    return new JobPenalty(
      $this->amount + $other->amount,
      $description
    );
  }

  function limit(int $minimumPercentage): void {
    $amount = 100 - $minimumPercentage;
    if ($this->amount > $amount) {
      $this->amount = $amount;
      $this->description .= ", limitat la {$amount}%";
    }
  }
}
