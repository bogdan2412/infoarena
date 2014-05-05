<?php

require_once(IA_ROOT_DIR . 'eval/BaseGrader.php');

class JavaGrader extends BaseGrader {
    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];

        $infile = $this->getInFile($jaildir);
        $userfile = 'Main.class';

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
        foreach (scandir(IA_ROOT_DIR.'eval/temp') as $file) {
            if (strpos($file, '.class') !== strlen($file) - 6) {
                continue;
            }

            eval_assert(@copy(IA_ROOT_DIR.'eval/temp/'.$file, $file),
                'Failed to copy user program');

        }

        // Run user program on a test case.
        $timelimit = $this->task['timelimit'] * 2000;
        $memlimit = $this->task['memlimit'] * 2;

        $jrunres = jail_run_java($jaildir,
                                (int)$timelimit,
                                (int)$memlimit,
                                array($this->task['id'].".in",
                                      $this->task['id'].".out"));

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

    protected function processUserSubmission() {
        $source_file = 'Main.java';
        $res = @file_put_contents($source_file,
                                  $this->job['file_contents']);
        eval_assert($res !== false,
                    'User program could not be written to disk');

        $compiler_messages = '';
        if (!compile_file($source_file, '', $compiler_messages)) {
            log_print('User program compile error');
            log_print($compiler_messages);
            throw new EvalUserCompileError($compiler_messages);
        }
        $this->result['log'] = "Compilare:\n" . $compiler_messages . "\n";
    }
}
