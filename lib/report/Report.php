<?php

abstract class Report {

  abstract function getDescription(): string;
  abstract function getVariable(): string;
  abstract function getTemplateName(): string;
  abstract function getLiveCount(): int;

  function getCachedCount(): int {
    return Variable::peek($this->getVariable(), 0);
  }

  function updateCount(): void {
    $count = $this->getLiveCount();
    Variable::poke($this->getVariable(), $count);
  }

  function getLinkName(): string {
    $className = get_class($this);
    return ReportUtil::classNameToUrlName($className);
  }

  function action(): void {
    // Children may implement this.
  }

}
