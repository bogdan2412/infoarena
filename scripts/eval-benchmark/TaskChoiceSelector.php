<?php

class TaskChoiceSelector {
  private array $taskChoiceMap;

  function __construct() {
    $this->taskChoiceMap = [];
  }

  function addChoices(array $taskChoices): void {
    foreach ($taskChoices as $tc) {
      $this->addChoice($tc);
    }
  }

  function addChoice(TaskChoice $tc): void {
    $this->taskChoiceMap[$tc->char] = $tc;
  }

  function chooseAction(): string {
    $keys = array_keys($this->taskChoiceMap);

    Log::default('Please make a choice:');
    foreach ($this->taskChoiceMap as $key => $taskChoice) {
      Log::default('%s   %s', [$key, $taskChoice->description], 1);
    }

    do {
      $choice = readline('Your choice? ');
    } while (!in_array($choice, $keys));

    return $this->taskChoiceMap[$choice]->action;
  }
}
