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

  static function fromArray(array $data) {
    return new self($data['oldTime'], $data['oldTle'], $data['newTime'], $data['newTle']);
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
