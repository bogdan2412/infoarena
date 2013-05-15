<?php

require_once(IA_ROOT_DIR . 'eval/BaseGrader.php');

class InteractiveGrader extends BaseGrader {
    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];

        $in_file = $this->getInFile($jaildir);
        $interact_file = 'interact';
        $interact_in_pipe = 'interact.in';
        $interact_out_pipe = 'interact.out';

        $user_jaildir = $jaildir . 'user/';
        $user_file = 'user_' . $this->job['id'] . '_' . $testno;
        $user_in_pipe = 'user.in';
        $user_out_pipe = 'user.out';

        // Download input file.
        if (!copy_grader_file($this->task, 'test' . $testno . '.in',
                              $in_file)) {
            log_print("Test $testno: input not found");
            throw new EvalTaskOwnerError(
                "Lipşeşte intrarea testului {$testno}.\nPagina cu " .
                "enunţul problemei trebuie să conţină un ataşament " .
                "'grader_test{$testno}.in'");
        }

        // Copy user executable
        eval_assert(clean_dir($user_jaildir), 'Failed to create user jail');
        eval_assert(@copy(IA_ROOT_DIR.'eval/temp/user',
                          $user_jaildir . $user_file),
                    'Failed to copy user program');
        eval_assert(@chmod($user_jaildir . $user_file, 0555),
                    'Failed to chmod a+x user program');

        // Copy interact executable
        eval_assert(@copy(IA_ROOT_DIR.'eval/temp/interact',
                          $jaildir . $interact_file),
                    'Failed to copy interact program');
        eval_assert(@chmod($jaildir . $interact_file, 0555),
                    'Failed to chmod a+x user program');

        // Create named pipes
        eval_assert(@posix_mkfifo($user_jaildir . $user_in_pipe, 0600) &&
                    @posix_mkfifo($user_jaildir . $user_out_pipe, 0600),
                    'Unable to create named pipes');
        eval_assert(@symlink($user_jaildir . $user_out_pipe,
                             $jaildir . $interact_in_pipe) &&
                    @symlink($user_jaildir . $user_in_pipe,
                             $jaildir . $interact_out_pipe),
                    'Unable to create user jail dir');

        // Run user program on a test case.
        $time_limit = ((int)$this->task['timelimit']) * 1000;
        $mem_limit = (int)$this->task['memlimit'];
        $wall_time_limit =
            $time_limit + IA_JUDGE_TASK_INTERACT_TIMELIMIT + 15000;
        $interact_process = jail_run(
            $interact_file, $jaildir,
            IA_JUDGE_TASK_INTERACT_TIMELIMIT,
            IA_JUDGE_TASK_INTERACT_MEMLIMIT, false,
            array('in' => $interact_in_pipe,
                  'out' => $interact_out_pipe,
                  'out-before-in' => true), true,
            '--wall-time-limit=' . $wall_time_limit);
        $user_process = jail_run(
            $user_file, $user_jaildir,
            $time_limit, $mem_limit, false,
            array('in' => $user_in_pipe,
                  'out' => $user_out_pipe), true,
            '--wall-time-limit=' . ($wall_time_limit + 100));
        list($interact_res, $user_res) =
            jrun_get_result_many(array($interact_process, $user_process));

        eval_assert($interact_res['result'] != 'ERROR',
                    'Error in jrun for interactive program: ' .
                    $interact_res['message']);
        eval_assert($user_res['result'] != 'ERROR',
                    'Error in jrun for user program: ' .
                    $user_res['message']);

        if ($interact_res['result'] !== 'OK') {
            log_warn("Test $testno: Interactive program failed: " .
                     $interact_res['message'] . ' ' . $interact_res['time'] .
                     'ms ' . $interact_res['memory'] . 'kb');
            throw new EvalTaskOwnerError(
                'Eroare in programul interactiv: ' .
                $interact_res['message']);
        }
        if ($user_res['result'] == 'FAIL') {
            log_print("Test $testno: User program failed: " .
                      $user_res['message'] . ' ' . $user_res['time'] .
                      'ms ' . $user_res['memory'] . 'kb');
            $message = $user_res['message'];
            $test_result = array(
                'test_time' => $user_res['time'],
                'test_mem' => $user_res['memory'],
                'grader_time' => null,
                'grader_mem' => null,
                'score' => 0,
                'message' => $message,
            );
            // User program failed on this test. Bye bye.
            return;
        }

        $test_result = array(
            'test_time' => $user_res['time'],
            'test_mem' => $user_res['memory'],
        );
        $this->testCaseJudgeOutputs($testno, $jaildir);
    }
}
