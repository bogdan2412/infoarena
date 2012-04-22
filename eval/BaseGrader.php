<?php

require_once(IA_ROOT_DIR."common/task.php");

abstract class BaseGrader {
    protected $task, $tparams, $job;
    protected $result, $testResults;

    public function __construct($task, $tparams, $job) {
        $this->task = $task;
        $this->tparams = $tparams;
        $this->job = $job;
    }

    /**
     * Compiles custom evaluator and interactive program if needed.
     * The evaluator is used when multiple correct solutions can be outputted,
     * in which case simply comparing files is not enough.
     * The interactive program is used in 'interactive' tasks. This program is
     * run in parallel with the user's program and comunication between them
     * is implemented through pipes.
     *
     * @return boolean          true on success, false on error
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
                $this->result = array(
                    'score' => 0,
                    'message' => 'Eroare in setarile problemei',
                    'log' => ("Lipseste $eval_desc.\n" .
                              "Ar trebui sa existe un atasament ".
                              "'grader_{$this->task[$eval_type]}' " .
                              "la pagina cu enuntul problemei"),
                );
                return false;
            }

            $compiler_messages = '';
            if (!compile_file($source_file, $eval_type, $compiler_messages)) {
                log_print("Task $eval_type compile error");
                $this->result = array(
                    'score' => 0,
                    'message' => "Eroare de compilare in $eval_desc",
                    'log' => ("Eroare de compilare in $eval_desc:\n" .
                              $compiler_messages),
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Processes the user submission. For classic and interactive tasks, this
     * means compiling the user's source file.
     *
     * @return boolean          true on success, false on error
     */
    protected function processUserSubmission() {
        $source_file = 'user.' . $this->job['compiler_id'];
        if (file_put_contents($source_file,
                              $this->job['file_contents']) === false) {
            log_print('User program could not be written to disk');
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare de sistem',
                'log' => 'Contacteaza un administrator',
            );
            return false;
        }

        $compiler_messages = '';
        if (!compile_file($source_file, 'user', $compiler_messages)) {
            log_print('User program compile error');
            log_print($compiler_messages);
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare de compilare',
                'log' => "Eroare de compilare:\n" . $compiler_messages,
            );
            return false;
        }
        $this->result['log'] = "Compilare:\n" . $compiler_messages . "\n";
        return true;
    }

    /**
     * Perform any necessary actions before running test cases.
     * These include compiling evaluators or interactive programs and
     * compiling user source files.
     *
     * @return boolean          true on success, false on error
     */
    protected function preTestCases() {
        $this->result = array(
            'score' => 0,
            'message' => 'Evaluare incompleta',
            'log' => '',
        );

        // Clean temporary directory and chdir to it
        $ret = clean_dir(IA_ROOT_DIR . 'eval/temp/');
        log_assert($ret, "Can't clean to temp dir.");
        $ret = chdir(IA_ROOT_DIR . 'eval/temp/');
        log_assert($ret, "Can't chdir to temp dir.");

        // Compile all source files
        if (!$this->compileEvaluators()) {
            return false;
        }
        if (!$this->processUserSubmission()) {
            return false;
        }
        return true;
    }

    /**
     * Perform any necessary actions after running all test cases.
     *
     * @return boolean          true on success, false on error
     */
    protected function postTestCases() {
        $this->result['message'] = 'Evaluare completa';
        if ($this->result['score'] < 0 ||
            $this->result['score'] > IA_JUDGE_MAX_SCORE) {
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare in evaluatorul problemei',
                'log' => 'Evaluatorul a returnat un scor invalid.',
            );
            return false;
        }
        return true;
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

    protected function getUserFile($jaildir, $testno) {
        // FIXME: jrun does not work with absolute paths because of chroot
        return 'user_' . $this->job['id'] . '_' . $testno;
    }

    /**
     * Evaluates the contestant's output on a particular test case.
     *
     * @param  int     $testno
     * @param  string  $jaildir
     * @return bool                  true on success, false on failure
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
                $this->result = array(
                    'score' => 0,
                    'message' => 'Eroare in testele problemei',
                    'log' => ("Lipseste fisierul .ok al testului $testno.\n" .
                              "Ar trebui sa existe un atasament".
                              " 'grader_test$testno.ok' ".
                              "la pagina cu enuntul problemei"),
                );
                return false;
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
            return true;
        }

        // Custom grader.
        $ret = copy(IA_ROOT_DIR . 'eval/temp/evaluator',
                    $jaildir . 'evaluator');
        log_assert($ret, "Failed copying custom grader");
        $ret = chmod('evaluator', 0555);
        log_assert("Failed to chmod a+x user program");

        // Run task eval, and check result
        $jrunres = jail_run('evaluator', $jaildir,
                            IA_JUDGE_TASK_EVAL_TIMELIMIT,
                            IA_JUDGE_TASK_EVAL_MEMLIMIT,
                            true);
        log_assert($jrunres['result'] != 'ERROR', "Error in jrun");

        // Task eval is not allowed to fail.
        if ($jrunres['result'] == 'FAIL') {
            log_print("Test $testno: Task eval failed");
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare in evaluatorul problemei',
                'log' => ("A aparut o eroare in rularea evaluatorului ".
                          "pe testul $testno: {$jrunres['message']}".
                          ": timp {$jrunres['time']}ms".
                          ": mem {$jrunres['memory']}kb"),
            );
            return false;
        }

        // Get score.
        $jrunres['stdout'] = trim($jrunres['stdout']);
        if ($jrunres['stdout'] === '' ||
            !is_whole_number($jrunres['stdout'])) {
            log_print("Test $testno: Task eval score broken or empty");
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare in evaluatorul problemei',
                'log' => ("Evaluatorul nu a returnat un numar la stdout ".
                          "pe testul $testno (se ignora spatii, newline, etc)"),
            );
            return false;
        }

        $test_result['grader_time'] = $jrunres['time'];
        $test_result['grader_mem'] = $jrunres['memory'];
        $test_result['score'] = (int)$jrunres['stdout'];
        if ($test_result['score'] < 0 ||
            $test_result['score'] > IA_JUDGE_MAX_SCORE) {
            log_print("Test $testno: Invalid score returned by evaluator");
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare in evaluatorul problemei',
                'log' => 'Evaluatorul a returnat un scor invalid.',
            );
            return false;
        }

        // Get message.
        $message = $jrunres['stderr'];
        $match = array();
        if (!preg_match("/^([^\n]*)/i", $message, $match)) {
            log_error("Broken preg_match???");
        }
        $message = $match[1];
        if (strpos($message, "\n") ||
            strlen($message) > IA_JUDGE_MAX_EVAL_MESSAGE) {
            log_print("Test $testno: Task eval message broken");
            $this->result = array(
                'score' => 0,
                'message' => 'Eroare in evaluatorul problemei',
                'log' =>
                    ('Evaluatorul a returnat un mesaj mai lung de ' .
                     IA_JUDGE_MAX_EVAL_MESSAGE . 'de caractere la stdout'),
            );
            return false;
        }
        $test_result['message'] = $message;

        // Log.
        log_print("Test $testno: Eval gave {$test_result['score']} points " .
                  "and said {$test_result['message']}");
        return true;
    }

    /**
     * Judges the user's submission on one test case. Depinding on task type,
     * it should copy the necessary input and user files, compile and run any
     * source files and call testCaseJudgeOutputs() afterwards
     *
     * @param  int     $testno
     * @param  string  $jaildir
     * @return bool                  true on success, false on failure
     */
    abstract protected function testCaseJudge($testno, $jaildir);

    /**
     * Grade the submission and return the result
     *
     * @return array            Array containing 'score', 'message' and 'log'
     *                          fields
     */
    public function grade() {
        if (!$this->preTestCases()) {
            return $this->result;
        }

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
                $ret = clean_dir($jaildir);
                log_assert($ret, "Can't clean jail dir.");
                $ret = chdir($jaildir);
                log_assert($ret, "Can't chdir to jail dir.");

                if (!$this->testCaseJudge($testno, $jaildir)) {
                    return $this->result;
                }
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
