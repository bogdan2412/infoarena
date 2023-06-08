<?php

class TimeInfo {
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
}
