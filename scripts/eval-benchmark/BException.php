<?php

class BException extends Exception {
  private array $args;

  function __construct(string $message, array $args = []) {
    parent::__construct($message);
    $this->args = $args;
  }

  function getArgs() {
    return $this->args;
  }
}
