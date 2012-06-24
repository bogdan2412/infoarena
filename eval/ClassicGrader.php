<?php

require_once(IA_ROOT_DIR . 'eval/BaseGrader.php');

class ClassicGrader extends BaseGrader {
    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];

        $infile = $this->getInFile($jaildir);
        $userfile = 'user_' . $this->job['id'] . '_' . $testno;

        // HACK: Capture run-time stderr for Python jobs.
        //
        // WARNING! This is a security hole! Users may dump input tests
        // on stderr and see them in the monitor page. Currently, only
        // admins are allowed to submit Python.
        //
        // TODO: Come up with a safe way of reporting run-time errors
        // for Python scripts.
        $capture_std = ('py' == $this->job['compiler_id']);

        // Download input file.
        if (!copy_grader_file($this->task, 'test' . $testno . '.in',
                              $infile)) {
            log_print("Test $testno: input not found");
            throw new EvalTaskOwnerError(
                "Lipşeşte intrarea testului {$testno}.\nPagina cu " .
                "enunţul problemei trebuie să conţină un ataşament " .
                "'grader_test{$testno}.in'");
        }

        // Copy user executable
        eval_assert(@copy(IA_ROOT_DIR.'eval/temp/user', $userfile),
                    'Failed to copy user program');
        eval_assert(@chmod($userfile, 0555),
                    'Failed to chmod a+x user program');

        // Run user program on a test case.
        $timelimit = $this->task['timelimit'] * 1000;
        $memlimit = $this->task['memlimit'];
        // Adjust time and memory limit for Python jobs.
        if ('py' == $this->job['compiler_id']) {
            $timelimit *= 4.0;
            $memlimit *= 2.0;
        }

        $jrunres = jail_run($userfile, $jaildir,
                            (int)$timelimit,
                            (int)$memlimit,
                            $capture_std);
        eval_assert($jrunres['result'] != 'ERROR',
                    'Error in jrun: ' . $jrunres['message']);
        if ($jrunres['result'] == 'FAIL') {
            log_print("Test $testno: User program failed: " .
                      $jrunres['message'] . ' ' . $jrunres['time'] .
                      'ms ' . $jrunres['memory'] . 'kb');
            if ($capture_std) {
                // TODO: Come up with a safe way of reporting run-time
                // errors for Python scripts.
                $message = ($jrunres['message'].": "
                            .substr($jrunres['stderr'],
                                    -min(100, strlen($jrunres['stderr']))));
            } else {
                $message = $jrunres['message'];
            }
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
