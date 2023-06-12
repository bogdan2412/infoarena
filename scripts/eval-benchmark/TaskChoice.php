<?php

class TaskChoice {
  public string $char;
  public string $description;
  public string $action;

  function __construct(string $char, string $description, string $action) {
    $this->char = $char;
    $this->description = $description;
    $this->action = $action;
  }
}
