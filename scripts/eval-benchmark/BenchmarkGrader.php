<?php

require_once(Config::ROOT . 'eval/ClassicGrader.php');
require_once(Config::ROOT . 'eval/Exceptions.php');
require_once(Config::ROOT . 'eval/download.php');
require_once(Config::ROOT . 'eval/utilities.php');

class BenchmarkGrader extends ClassicGrader {
  const JAIL_DIR = Config::ROOT . 'eval/jail/';

  function __construct(array $task, array $job) {
    // Don't get hung up on memory constraints. They may have to do with
    // 64- versus 32- bit architectures. Just give the program another MB.
    $task['params']['memlimit'] += 1024;
    parent::__construct($task, $task['params'], $job);
  }

  function compileJobSource(): void {
    parent::processUserSubmission();
  }

  /**
   * Runs the job on a single test. Adapted from BaseGrader::grade() and
   * ClassicGrader::testCaseJudge(). Note that, even if a test passed on the
   * old hardware, it may still fail on the new one (e.g. job #552652).
   **/
  function runTest(): NewResult {
    eval_assert(clean_dir(self::JAIL_DIR), "Can't clean jail dir.");
    eval_assert(chdir(self::JAIL_DIR), "Can't chdir to jail dir.");
    $infile = $this->getInFile(self::JAIL_DIR);
    $info = $this->runTestCase(
      WorkStack::getTestNo(),
      self::JAIL_DIR,
      $infile
    );

    if ($info['result'] == 'OK') {
      $status = NewResult::ST_OK;
    } else if (preg_match('/time limit/i', $info['message']))  {
      $status = NewResult::ST_TLE;
    } else {
      $status = NewResult::ST_OTHER;
    }

    return new NewResult(
      $status,
      (float)$info['time'] / 1000,
      $info['message']
    );
  }
}
