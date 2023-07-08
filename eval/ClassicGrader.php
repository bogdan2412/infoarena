<?php

require_once(Config::ROOT . 'eval/BaseGrader.php');

class ClassicGrader extends BaseGrader {

    /**
     * Downloads the test file and runs the user binary.
     * @return array The information from the sandbox.
     **/
    protected function runTestCase($testno, $jaildir, $infile): array {
        // Download input file.
        if (!copy_grader_file($this->task, 'test' . $testno . '.in',
                              $infile)) {
            log_print("Test $testno: input not found");
            throw new EvalTaskOwnerError(
                "Lipsește intrarea testului {$testno}.\nPagina cu " .
                "enunțul problemei trebuie să conțină un atașament " .
                "'grader_test{$testno}.in'");
        }

        // Run user program on a test case.
        $timelimit = $this->task['timelimit'] * 1000;
        $memlimit = $this->task['memlimit'];

        $jrunres = run_file($this->job['compiler_id'],
                            Config::ROOT.'eval/temp/user',
                            $jaildir,
                            (int)$timelimit,
                            (int)$memlimit,
                            IA_CACHE_MEMORY);
        eval_assert($jrunres['result'] != 'ERROR',
                    'Error in jrun: ' . $jrunres['message']);
        return $jrunres;
    }

    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];

        $infile = $this->getInFile($jaildir);
        $jrunres = $this->runTestCase($testno, $jaildir, $infile);

        if ($jrunres['result'] == 'FAIL') {
            log_print("Test $testno: User program failed: " .
                      $jrunres['message'] . ' ' . $jrunres['time'] .
                      'ms ' . $jrunres['memory'] . 'kb');
            $message = $jrunres['message'];
            $test_result = array(
                'test_time' => $jrunres['time'],
                'test_mem' => $jrunres['memory'],
                'grader_time' => null,
                'grader_mem' => null,
                'score' => 0,
                'message' => $message,
            );
            // User program failed on this test. Bye bye.
            return;
        }

        $test_result = array(
            'test_time' => $jrunres['time'],
            'test_mem' => $jrunres['memory'],
        );
        $this->testCaseJudgeOutputs($testno, $jaildir);
    }
}
