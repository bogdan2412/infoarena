<?php

class Args {
  private string $checkpointDir;
  private string $taskId;

  function parse() {
    $opts = getopt('c:t:');
    if (empty($opts)) {
      $this->usage();
      exit(1);
    }
    $this->checkpointDir = $opts['c'] ?? '';
    $this->taskId = $opts['t'] ?? '';
  }

  private function usage() {
    $scriptName = $_SERVER['SCRIPT_FILENAME'];
    print "Usage: $scriptName -c <dir> [-t <task>]\n";
    print "\n";
    print "    -c <dir>:   Use this directory to read and write checkpoint files.\n";
    print "    -t <task>:  Benchmark only this task. If empty, benchmark all tasks.\n";
    print "                in alphabetical order.\n";
  }

  function getCheckpointDir(): string {
    return $this->checkpointDir;
  }

  function getTaskId(): string {
    return $this->taskId;
  }
}
