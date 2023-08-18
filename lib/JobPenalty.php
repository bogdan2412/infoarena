<?php

class JobPenalty {
  public int $amount;
  public string $description;

  function __construct(int $amount, string $description) {
    $this->amount = $amount;
    $this->description = $description;
  }

  function add(JobPenalty $other): JobPenalty {
    $descriptions = [ $this->description, $other->description ];
    return new JobPenalty(
      $this->total + $other->total,
      implode(' + ', $descriptions)
    );
  }
}
