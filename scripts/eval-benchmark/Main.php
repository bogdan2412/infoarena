<?php

class Main {
  private Args $args;
  private Checkpointer $checkpointer;
  private Database $db;
  private array $tasks;

  public function run() {
    $this->parseCommandLineArgs();
    $this->checkUsage();
    $this->setupComponents();
    $this->loadTasks();
    $this->benchmarkAllTasks();
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

  function setupComponents() {
    $this->checkpointer = new Checkpointer($this->args->getCheckpointDir());
    $this->db = new Database();
    $this->db->loadAdmins();
  }

  function loadTasks() {
    $taskId = $this->args->getTaskId();
    $this->tasks = ($taskId)
      ? [ $this->db->loadTaskById($taskId) ]
      : $this->db->loadTasks();
  }

  function benchmarkAllTasks() {
    $numTasks = count($this->tasks);
    foreach ($this->tasks as $ord => $task) {
      $tb = new TaskBenchmark($this->checkpointer, $this->db, $task,
                              1 + $ord, $numTasks);
      $tb->run();
    }
  }

}
