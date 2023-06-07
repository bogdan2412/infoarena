<?php

class Main {
  private Args $args;

  public function run() {
    try {
      $this->parseCommandLineArgs();
      $this->checkUsage();
      $cp = new Checkpointer($this->args->getCheckpointDir());
      $db = new Database();
      $taskId = $this->args->getTaskId();
      $tb = new TaskBenchmark($cp, $db, $taskId);
      $tb->run();
    } catch (BException $e) {
      Log::fatal($e->getMessage(), $e->getArgs());
    }
  }

  function parseCommandLineArgs() {
    $this->args = new Args();
    $this->args->parse();
  }

  function checkUsage() {
    $this->checkRootAccess();
    $this->warnIfNoisyLogLevel();
  }

  function checkRootAccess() {
    if (exec('whoami') != 'root') {
      throw new BException('This script MUST be run as root.');
    }
  }

  function warnIfNoisyLogLevel() {
    if (IA_ERROR_REPORTING & E_USER_NOTICE) {
      Log::warn('We advise changing this value in config.php:');
      Log::warn("\n    define('IA_ERROR_REPORTING', E_ALL & ~E_USER_NOTICE);\n");
      Log::warn('Allowing E_USER_NOTICE will clutter this script\'s log with jail info.');
    }
  }
}
