<?php

require_once(IA_ROOT_DIR . 'eval/BaseGrader.php');

class InteractiveGrader extends BaseGrader {
    protected function testCaseJudge($testno, $jaildir) {
        $this->testResults[$testno] = array();
        $test_result = &$this->testResults[$testno];

        $in_file = $this->getInFile($jaildir);

        $user_jaildir = $jaildir . 'user/';
        $user_in_pipe = $user_jaildir.'user.in.pipe';
        $user_out_pipe = $user_jaildir.'user.out.pipe';

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

        // Create named pipes
        eval_assert(@posix_mkfifo($user_in_pipe, 0600) &&
                    @posix_mkfifo($user_out_pipe, 0600),
                    'Unable to create named pipes');

        // Run user program on a test case.
        $time_limit = $this->task['timelimit'] * 1000;
        $mem_limit = (int)$this->task['memlimit'];
        $wall_time_limit =
            $time_limit + IA_JUDGE_TASK_INTERACT_TIMELIMIT + 1000;

        $interact_process = run_file(
            $this->evaluatorCompilerId['interact'],
            IA_ROOT_DIR.'eval/tmpfs/temp/evaluators/interact',
            $jaildir,
            array(
                'user' => IA_JUDGE_TASK_INTERACT_TIMELIMIT,
                'wall' => $wall_time_limit + 1000,
            ), IA_JUDGE_TASK_INTERACT_MEMLIMIT, false,
            array(
            'in' => $user_out_pipe,
                  'out' => $user_in_pipe,
                  'out-before-in' => true,
            ),
            'interact', true);

        $user_process = run_file(
            $this->job['compiler_id'],
            IA_ROOT_DIR.'eval/tmpfs/temp/user',
            $user_jaildir,
            array(
                'user' => $time_limit,
                'wall' => $wall_time_limit,
            ), $mem_limit, false,
            array('in' => $user_in_pipe,
            'out' => $user_out_pipe,
            ),
            'default', true);
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
