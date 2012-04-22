<?php

require_once(IA_ROOT_DIR . 'eval/BaseGrader.php');

class ClassicGrader extends BaseGrader {
    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];
        $infile = $this->getInFile($jaildir);
        $userfile = $this->getUserFile($jaildir, $testno);

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
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare in teste',
                'log' => ("Lipseste intrarea testului $testno.\n" .
                          "Ar trebui sa existe un atasament".
                          " 'grader_test$testno.in' ".
                          "la pagina cu enuntul problemei"),
            );
            return false;
        }

        $ret = copy(IA_ROOT_DIR.'eval/temp/user', $userfile);
        log_assert($ret, "Failed copying user program");
        $ret = chmod($userfile, 0555);
        log_assert("Failed to chmod a+x user program");

        // Run user program on a test case.
        $timelimit = $this->tparams['timelimit'] * 1000;
        $memlimit = $this->tparams['memlimit'];
        // Adjust time and memory limit for Python jobs.
        if ('py' == $this->job['compiler_id']) {
            $timelimit *= 4.0;
            $memlimit *= 2.0;
        }

        $jrunres = jail_run($userfile, $jaildir,
                            (int)$timelimit,
                            (int)$memlimit,
                            $capture_std);
        log_assert($jrunres['result'] != 'ERROR',
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
            return true;
        }

        $test_result = array(
            'test_time' => $jrunres['time'],
            'test_mem' => $jrunres['memory'],
        );
        if (!$this->testCaseJudgeOutputs($testno, $jaildir)) {
            return false;
        }
        return true;
    }
}
