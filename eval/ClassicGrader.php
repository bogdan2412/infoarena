<?php

require_once(IA_ROOT_DIR . 'eval/BaseGrader.php');

class ClassicGrader extends BaseGrader {
    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];

        $infile = $this->getInFile($jaildir);

        // Download input file.
        if (!copy_grader_file($this->task, 'test' . $testno . '.in',
                              $infile)) {
            log_print("Test $testno: input not found");
            throw new EvalTaskOwnerError(
                "Lipşeşte intrarea testului {$testno}.\nPagina cu " .
                "enunţul problemei trebuie să conţină un ataşament " .
                "'grader_test{$testno}.in'");
        }

        // Run user program on a test case.
        $timelimit = $this->task['timelimit'] * 1000;
        $memlimit = $this->task['memlimit'];

        $jrunres = run_file($this->job['compiler_id'],
                            IA_ROOT_DIR.'eval/tmpfs/temp/user',
                            $jaildir, (int)$timelimit, (int)$memlimit);
        eval_assert($jrunres['result'] != 'ERROR',
                    'Error in jrun: ' . $jrunres['message']);
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
