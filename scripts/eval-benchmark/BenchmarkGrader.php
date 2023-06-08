<?php

require_once(IA_ROOT_DIR . 'eval/ClassicGrader.php');
require_once(IA_ROOT_DIR . 'eval/download.php');
require_once(IA_ROOT_DIR . 'eval/utilities.php');

class BenchmarkGrader extends ClassicGrader {
  const JAIL_DIR = IA_ROOT_DIR . 'eval/jail/';

  function __construct(array $task, array $task_params, array $job) {
    // Don't get hung up on memory constraints. They may have to do with
    // 64- versus 32- bit architectures. Just give the program another MB.
    $task_params['memlimit'] += 1024;
    parent::__construct($task, $task_params, $job);
  }

  function compileJobSource(): void {
    parent::processUserSubmission();
  }

  /**
   * Runs the job on a single test. Adapted from BaseGrader::grade() and
   * ClassicGrader::testCaseJudge(). Note that, even if a test passed on the
   * old hardware, it may still fail on the new one (e.g. job #552652).
   *
   * Returns an array of:
   *   - status:  one of the ST_* constants;
   *   - time:    relayed from the sandbox, converted to seconds;
   *   - message: relayed from the sandbox.
   **/
  function runTest(array &$test): TestResult {
    eval_assert(clean_dir(self::JAIL_DIR), "Can't clean jail dir.");
    eval_assert(chdir(self::JAIL_DIR), "Can't chdir to jail dir.");
    $infile = $this->getInFile(self::JAIL_DIR);
    $info = $this->runTestCase(
      $test['test_number'],
      self::JAIL_DIR,
      $infile
    );

    if ($info['result'] == 'OK') {
      $status = TestResult::ST_OK;
    } else if (preg_match('/time limit/i', $info['message']))  {
      $status = TestResult::ST_TLE;
    } else {
      $status = TestResult::ST_OTHER;
    }

    return new TestResult(
      $status,
      (float)$info['time'] / 1000,
      $info['message']
    );
  }
}
