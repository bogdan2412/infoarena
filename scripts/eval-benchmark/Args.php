<?php

class Args {
  private string $checkpointDir;
  private string $taskId;
  private bool $batchMode;
  private bool $reportMode;
  private bool $sqlMode;

  function parse() {
    $opts = getopt('bc:rst:');
    if (empty($opts)) {
      $this->usage();
      exit(1);
    }
    $this->checkpointDir = $opts['c'] ?? '';
    $this->taskId = $opts['t'] ?? '';
    $this->batchMode = isset($opts['b']);
    $this->reportMode = isset($opts['r']);
    $this->sqlMode = isset($opts['s']);
    $this->validate();
  }

  private function usage() {
    $scriptName = $_SERVER['SCRIPT_FILENAME'];
    print "Usage: $scriptName -c <dir> [-t <task>] [-b|-r]\n";
    print "\n";
    print "    -b:         Benchmark only, in batch mode (non-interactive).\n";
    print "    -c <dir>:   Use this directory to read and write checkpoint files.\n";
    print "    -r:         Print a report of data computed so far.\n";
    print "    -s:         Print SQL statements for the proposed changes.\n";
    print "    -t <task>:  Benchmark only this task. If empty, benchmark all tasks.\n";
    print "                in alphabetical order.\n";
  }

  private function validate() {
    if (!$this->checkpointDir) {
      throw new BException(
        "Please specify a checkpoint directory with -c <dir>, e.g. -c /tmp/benchmark.\n" .
        'This allows us to save/restore progress.');
    }
    if ($this->reportMode && $this->batchMode) {
      throw new BException('The options -b and -r are incompatible.');
    }
    if ($this->reportMode && $this->sqlMode) {
      throw new BException('The options -r and -s are incompatible.');
    }
  }

  function getCheckpointDir(): string {
    return $this->checkpointDir;
  }

  function getTaskId(): string {
    return $this->taskId;
  }

  function getBatchMode(): bool {
    return $this->batchMode;
  }

  function getReportMode(): bool {
    return $this->reportMode;
  }

  function getSqlMode(): bool {
    return $this->sqlMode;
  }
}
