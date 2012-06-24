<?php

require_once(IA_ROOT_DIR."common/task.php");

abstract class BaseGrader {
    protected $task, $job;
    protected $result, $testResults;

    public function __construct($task, $tparams, $job) {
        $this->task = array_merge($task, $tparams);
        $this->job = $job;
    }

    /**
     * Compiles custom evaluator and interactive program if needed.
     * The evaluator is used when multiple correct solutions can be outputted,
     * in which case simply comparing files is not enough.
     * The interactive program is used in 'interactive' tasks. This program is
     * run in parallel with the user's program and comunication between them
     * is implemented through pipes.
     */
    protected function compileEvaluators() {
        $evals = array(
            'evaluator' => 'evaluatorul problemei',
            'interact' => 'programul interactiv',
        );
        foreach ($evals as $eval_type => $eval_desc) {
            if (!getattr($this->task, $eval_type)) {
                continue;
            }

            $source_file = IA_ROOT_DIR . 'eval/temp/' . $this->task[$eval_type];
            if (!copy_grader_file($this->task, $this->task[$eval_type],
                                  $source_file)) {
                log_print("Task $eval_type not found");
                throw new EvalTaskOwnerError(
                    "Lipşeşte {$eval_desc}.\nPagina cu enunţul problemei " .
                    "trebuie să conţină un ataşament 'grader_" .
                    $this->task[$eval_type] . "'");
            }

            $compiler_messages = '';
            if (!compile_file($source_file, $eval_type, $compiler_messages)) {
                log_print("Task $eval_type compile error");
                throw new EvalTaskOwnerError('Eroare de compilare în ' .
                                             $eval_desc . ":\n" .
                                             $compiler_messages);
            }
        }
    }

    /**
     * Processes the user submission. For classic and interactive tasks, this
     * means compiling the user's source file.
     */
    protected function processUserSubmission() {
        $source_file = 'user.' . $this->job['compiler_id'];
        $res = @file_put_contents($source_file,
                                  $this->job['file_contents']);
        eval_assert($res !== false,
                    'User program could not be written to disk');

        $compiler_messages = '';
        if (!compile_file($source_file, 'user', $compiler_messages)) {
            log_print('User program compile error');
            log_print($compiler_messages);
            throw new EvalUserCompileError($compiler_messages);
        }
        $this->result['log'] = "Compilare:\n" . $compiler_messages . "\n";
    }

    /**
     * Perform any necessary actions before running test cases.
     * These include compiling evaluators or interactive programs and
     * compiling user source files.
     */
    protected function preTestCases() {
        $this->result = array(
            'score' => 0,
            'message' => 'Evaluare incompleta',
            'log' => '',
        );

        // Clean temporary directory and chdir to it
        eval_assert(clean_dir(IA_ROOT_DIR . 'eval/temp/'),
                    "Can't clean temp dir.");
        eval_assert(@chdir(IA_ROOT_DIR . 'eval/temp/'),
                    "Can't chdir to temp dir.");

        // Compile all source files
        $this->compileEvaluators();
        $this->processUserSubmission();
    }

    /**
     * Perform any necessary actions after running all test cases.
     */
    protected function postTestCases() {
        $this->result['message'] = 'Evaluare completa';
        if ($this->result['score'] < 0 ||
            $this->result['score'] > IA_JUDGE_MAX_SCORE) {
            throw new EvalTaskOwnerError(
                'Evaluatorul a returnat un scor invalid.');
        }
    }

    protected function getInFile($jaildir) {
        return $jaildir . $this->task['id'] . '.in';
    }

    protected function getOutFile($jaildir) {
        return $jaildir . $this->task['id'] . '.out';
    }

    protected function getOkFile($jaildir) {
        return $jaildir . $this->task['id'] . '.ok';
    }

    /**
     * Evaluates the contestant's output on a particular test case.
     *
     * @param  int     $testno
     * @param  string  $jaildir
     */
    protected function testCaseJudgeOutputs($testno, $jaildir) {
        $test_result = &$this->testResults[$testno];
        $outfile = $this->getOutFile($jaildir);
        $okfile = $this->getOkFile($jaildir);

        // Copy ok file, if used.
        if ($this->task['use_ok_files']) {
            if (!copy_grader_file($this->task, 'test' . $testno . '.ok',
                                  $okfile)) {
                log_print("Test $testno: .ok file not found");
                throw new EvalTaskOwnerError(
                    "Lipşeşte fişierul .ok al testului $testno.\nPagina cu " .
                    "enunţul problemei trebuie să conţină un ataşament " .
                    "'grader_test{$testno}.ok'");
            }
        }

        if (!$this->task['evaluator']) {
            // Diff grading, trivial.
            $test_result['grader_time'] = null;
            $test_result['grader_mem'] = null;
            if (is_readable($outfile)) {
                $diff_output = shell_exec("diff -qBbEa $outfile $okfile");
                if ($diff_output == '') {
                    log_print("Test $testno: Diff eval: Files identical");
                    $test_result['message'] = 'OK';
                    $test_result['score'] = 100 / $this->task['test_count'];
                } else {
                    log_print("Test $testno: Diff eval: Files differ");
                    $test_result['message'] = 'Incorect';
                    $test_result['score'] = 0;
                }
            } else {
                log_print("Test $testno: Diff eval: output missing");
                $test_result['message'] = 'Fisier de iesire lipsa';
                $test_result['score'] = 0;
            }
            return;
        }

        // Custom grader.
        $ret = @copy(IA_ROOT_DIR . 'eval/temp/evaluator',
                     $jaildir . 'evaluator');
        eval_assert($ret, 'Failed to copy custom grader');
        eval_assert(chmod('evaluator', 0555),
                    'Failed to chmod a+x user program');

        // Run task eval, and check result
        $jrunres = jail_run('evaluator', $jaildir,
                            IA_JUDGE_TASK_EVAL_TIMELIMIT,
                            IA_JUDGE_TASK_EVAL_MEMLIMIT,
                            true);
        eval_assert($jrunres['result'] != 'ERROR', "Error in jrun");

        // Task eval is not allowed to fail.
        if ($jrunres['result'] == 'FAIL') {
            log_print("Test $testno: Task eval failed");
            throw new EvalTaskOwnerError(
                "A apărut o eroare în rularea evaluatorului pe testul " .
                "$testno: {$jrunres['message']}: timp {$jrunres['time']}ms: " .
                "mem {$jrunres['memory']}kb");
        }

        // Get score.
        $jrunres['stdout'] = trim($jrunres['stdout']);
        if ($jrunres['stdout'] === '' ||
            !is_whole_number($jrunres['stdout'])) {
            log_print("Test $testno: Task eval score broken or empty");
            throw new EvalTaskOwnerError(
                "Evaluatorul nu a returnat un număr la stdout " .
                "pe testul $testno (se ignoră spaţii, newline, etc)");
        }

        $test_result['grader_time'] = $jrunres['time'];
        $test_result['grader_mem'] = $jrunres['memory'];
        $test_result['score'] = (int)$jrunres['stdout'];
        if ($test_result['score'] < 0 ||
            $test_result['score'] > IA_JUDGE_MAX_SCORE) {
            log_print("Test $testno: Invalid score returned by evaluator");
            throw new EvalTaskOwnerError(
                'Evaluatorul a returnat un scor invalid.');
        }

        // Get message.
        $message = $jrunres['stderr'];
        $message = strtok($message, "\n");
        if (strlen($message) == 0 ||
            strlen($message) > IA_JUDGE_MAX_EVAL_MESSAGE) {
            log_print("Test $testno: Task eval message broken");
            throw new EvalTaskOwnerError(
                'Evaluatorul a returnat un mesaj gol sau mai lung de ' .
                IA_JUDGE_MAX_EVAL_MESSAGE . 'de caractere la stdout');
        }
        $test_result['message'] = $message;

        // Log.
        log_print("Test $testno: Eval gave {$test_result['score']} points " .
                  "and said {$test_result['message']}");
    }

    /**
     * Judges the user's submission on one test case. Depinding on task type,
     * it should copy the necessary input and user files, compile and run any
     * source files and call testCaseJudgeOutputs() afterwards
     *
     * @param  int     $testno
     * @param  string  $jaildir
     */
    abstract protected function testCaseJudge($testno, $jaildir);

    /**
     * Grade the submission and return the result
     *
     * @return array            Array containing 'score', 'message' and 'log'
     *                          fields
     */
    public function grade() {
        $this->preTestCases();

        // Running tests.
        $this->testResults = array();
        $test_score = array();

        $test_groups = task_get_testgroups($this->task);
        $group_idx = 0;
        foreach ($test_groups as $group) {
            $group_idx++;
            foreach ($group as $testno) {
                if (IA_JUDGE_KEEP_JAILS) {
                    $jaildir = (IA_ROOT_DIR . 'eval/jail/' .
                                $this->job['id'] . '-' . $testno . '/');
                } else {
                    $jaildir = IA_ROOT_DIR . "eval/jail/";
                }

                // Clean and chdir to jail dir.
                eval_assert(clean_dir($jaildir), "Can't clean jail dir.");
                eval_assert(@chdir($jaildir), "Can't chdir to jail dir.");

                $this->testCaseJudge($testno, $jaildir);
                $test_result = &$this->testResults[$testno];
                job_test_update($this->job['id'], $testno, $group_idx,
                                $test_result['test_time'],
                                $test_result['test_mem'],
                                $test_result['grader_time'],
                                $test_result['grader_mem'],
                                $test_result['score'], $test_result['message']);
                $test_score[$testno] = $test_result['score'];
            }

            $solved_group = true;
            $group_score = 0;
            foreach ($group as $testno) {
                if ($test_score[$testno] == 0) {
                    $solved_group = false;
                }
                $group_score += $test_score[$testno];
            }
            if ($solved_group) {
                $this->result['score'] += $group_score;
            }
        }

        $this->postTestCases();
        return $this->result;
    }
}
