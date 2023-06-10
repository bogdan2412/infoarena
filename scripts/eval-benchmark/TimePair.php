<?php

class TimePair {
  public float $oldTime;
  public bool $oldTle;
  public float $newTime;
  public bool $newTle;

  function __construct(float $oldTime, bool $oldTle, float $newTime, bool $newTle) {
    $this->oldTime = $oldTime;
    $this->oldTle = $oldTle;
    $this->newTime = $newTime;
    $this->newTle = $newTle;
  }

  function __construct(array $data) {
    $this->oldTime = $data['oldTime'];
    $this->oldTle = $data['oldTle'];
    $this->newTime = $data['newTime'];
    $this->newTle = $data['newTle'];
  }

  function asArray(): array {
    return [
      'oldTime' => $this->oldTime,
      'oldTle' => $this->oldTle,
      'newTime' => $this->newTime,
      'newTle' => $this->newTle,
    ];
  }
}
