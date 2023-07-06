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
    if ($this->args->getReportMode() || $this->args->getSqlMode()) {
      $this->printReport();
    } else {
      $this->benchmarkAllTasks();
    }
  }

  private function parseCommandLineArgs() {
    $this->args = new Args();
    $this->args->parse();
  }

  private function checkUsage() {
    $this->checkRootAccess();
    $this->warnIfNoisyLogLevel();
  }

  private function checkRootAccess() {
    if (exec('whoami') != 'root') {
      throw new BException('This script MUST be run as root.');
    }
  }

  private function warnIfNoisyLogLevel() {
    if (IA_ERROR_REPORTING & E_USER_NOTICE) {
      Log::warn('We advise changing this value in config.php:');
      Log::warn("\n    define('IA_ERROR_REPORTING', E_ALL & ~E_USER_NOTICE);\n");
      Log::warn('Allowing E_USER_NOTICE will clutter this script\'s log with jail info.');
    }
  }

  private function setupComponents() {
    $this->checkpointer = new Checkpointer($this->args->getCheckpointDir());
    $this->db = new Database();
    $this->db->loadUsers();
  }

  private function loadTasks() {
    $taskId = $this->args->getTaskId();
    $this->tasks = ($taskId)
      ? [ $this->db->loadTaskById($taskId) ]
      : $this->db->loadTasks();
  }

  private function printReport() {
    $sqlMode = $this->args->getSqlMode();
    $reporter = new Reporter($this->tasks, $this->checkpointer, $sqlMode);
    $reporter->run();
  }

  private function benchmarkAllTasks() {
    WorkStack::setTaskCount(count($this->tasks));
    $batchMode = $this->args->getBatchMode();

    foreach ($this->tasks as $task) {
      $tb = new TaskBenchmark($task, $this->db, $this->checkpointer, $batchMode);
      $tb->run();
    }
  }

}
