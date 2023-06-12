<?php

class TaskCheckpoint {
  public bool $skipped;
  public bool $benchmarked;
  public array $timePairs;
  public float $acceptedTimeLimit;

  function __construct() {
    $this->skipped = false;
    $this->benchmarked = false;
    $this->timePairs = [];
    $this->acceptedTimeLimit = 0.0;
  }

  static function fromArray(array $data) {
    $obj = new self();
    $obj->skipped = $data['skipped'];
    $obj->benchmarked = $data['benchmarked'];
    $obj->acceptedTimeLimit = $data['acceptedTimeLimit'];
    $obj->timePairs = $obj->timePairsFromArray($data['timePairs']);
    return $obj;
  }

  private function timePairsFromArray($data) {
    $result = [];
    foreach ($data as $row) {
      $result[] = TimePair::fromArray($row);
    }
    return $result;
  }

  function asArray(): array {
    return [
      'skipped' => $this->skipped,
      'benchmarked' => $this->benchmarked,
      'timePairs' => $this->timePairsAsArray(),
      'acceptedTimeLimit' => $this->acceptedTimeLimit,
    ];
  }

  private function timePairsAsArray(): array {
    $result = [];
    foreach ($this->timePairs as $timePair) {
      $result[] = $timePair->asArray();
    }
    return $result;
  }
}
