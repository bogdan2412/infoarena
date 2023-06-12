<?php

class Args {
  private string $checkpointDir;
  private string $taskId;
  private bool $batchMode;

  function parse() {
    $opts = getopt('c:t:b');
    if (empty($opts)) {
      $this->usage();
      exit(1);
    }
    $this->checkpointDir = $opts['c'] ?? '';
    $this->taskId = $opts['t'] ?? '';
    $this->batchMode = isset($opts['b']);
  }

  private function usage() {
    $scriptName = $_SERVER['SCRIPT_FILENAME'];
    print "Usage: $scriptName -c <dir> [-t <task>]\n";
    print "\n";
    print "    -c <dir>:   Use this directory to read and write checkpoint files.\n";
    print "    -t <task>:  Benchmark only this task. If empty, benchmark all tasks.\n";
    print "                in alphabetical order.\n";
    print "    -b:         Benchmark only, in batch mode (non-interactive).\n";
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
}
