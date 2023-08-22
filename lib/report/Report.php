<?php

abstract class Report {

  abstract function getDescription(): string;
  abstract function getVariable(): string;
  abstract function getLiveCount(): int;

  function getCachedCount(): int {
    return Variable::peek($this->getVariable());
  }

  function updateCount(): void {
    $count = $this->getLiveCount();
    Variable::poke($this->getVariable(), $count);
  }

  function getLinkName(): string {
    return get_class($this);
  }

}
