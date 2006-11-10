<?php

class ClassicGrader {
    public $task_id;

    public $test_count;

    public $time_limit;

    public $memory_limit;

    public $uses_ok_files;

    public $unique_output;

    public $evaluator;

    function __construct($id, $parameters) {
        $this->task_id = $id;

        if (!task_validate_parameters("classic", $parameters)) {
            log_warn("Invalid task parameters");
        }
        $this->evaluator = (string)$parameters['evaluator'];
        $this->test_count = (int)$parameters['tests'];
        $this->time_limit = (float)$parameters['timelimit'];
        $this->memory_limit = (int)$parameters['memlimit'];
        $this->unique_output = (bool)$parameters['unique_output'];
        $this->has_ok_files = (bool)$parameters['okfiles'];

        if ($this->unique_output == true && $this->has_ok_files == false) {
            log_warn("Task has unique output but no ok files");
        }

        if ($this->unique_output == true && $this->evaluator != "") {
            log_warn("Task has both unique output and evaluator");
        }
    }

    function Grade($file_contents, $file_extension) {
        $result = new JobResult();
        $result->score = 0;

        // Clean jail and temp
        if (!clean_dir(IA_EVAL_TEMP_DIR)) {
            log_warn("Can't clean to temp dir.");
            return JobResult::SystemError();
        }

        // chdir to temp dir.
        if (!@chdir(IA_EVAL_TEMP_DIR)) {
            log_warn("Can't chdir to temp dir.");
            return JobResult::SystemError();
        }

        // Compile custom evaluator.
        if (!$this->unique_output) {
            if (!copy_grader_file($this->task_id, $this->evaluator,
                    IA_EVAL_TEMP_DIR . $this->evaluator)) {
                return JobResult::SystemError();
            }

            if (!compile_file($this->evaluator , $compiler_messages)) {
                log_warn("Can't compile evaluator.");
                return JobResult::SystemError();
            }
        }

        // Compile user source.
        if (!@file_put_contents("user." . $file_extension, $file_contents)) {
            log_warn("Can't write user file on disk.");
            return JobResult::SystemError();
        }
        if (!compile_file("user." . $file_extension, $compiler_messages)) {
            if ($compiler_messages === false) {
                return JobResult::SystemError();
            }
            $result->message = "Eroare de compilare";
            $result->log = "Eroare de compilare:\n" . $compiler_messages;
        } else {
            $result->log = "Compilare:\n" . $compiler_messages . "\n";
        }

        // Running tests.
        for ($testno = 1; $testno <= $this->test_count; ++$testno) {
            $result->log .= "\nRulez testul $testno: ";

            if (!@chdir(IA_EVAL_DIR)) {
                log_warn("Can't chdir to eval dir.");
                return JobResult::SystemError();
            }
            if (!clean_dir(IA_EVAL_JAIL_DIR)) {
                return JobResult::SystemError();
            }
            if (!@chdir(IA_EVAL_JAIL_DIR)) {
                log_warn("Can't chdir to jail dir.");
                return JobResult::SystemError();
            }

            if (!copy_grader_file($this->task_id, 'test' . $testno . '.in',
                        IA_EVAL_JAIL_DIR . $this->task_id . '.in')) {
                return JobResult::SystemError();
            }

            if (!@copy(IA_EVAL_TEMP_DIR . 'user', IA_EVAL_JAIL_DIR . 'user')) {
                log_warn("Failed copying user program");
                return JobResult::SystemError();
            }
            @system("chmod a+x user", $res);
            if ($res) {
                log_warn("Failed to chmod a+x user program");
                return JobResult::SystemError();
            }
         
            // Run user program.
            $jrunres = jail_run('user', $this->time_limit * 1000, $this->memory_limit);
            log_print("JRUN user: ".$jrunres['result'].": ".$jrunres['message']);
            if ($jrunres['result'] == 'ERROR') {
                return JobResult::SystemError();
            } else if ($jrunres['result'] == 'FAIL') {
                $result->log .= "eroare: ".$jrunres['message'].": 0 puncte";
                log_print("");
                continue;
            } else {
                $result->log .= "ok: timp ".$jrunres['time']."ms ".
                        $jrunres['memory']."kb: ";
            }

            // Copy ok file, if used.
            if ($this->has_ok_files) {
                if (!copy_grader_file($this->task_id , 'test' . $testno . '.ok',
                            IA_EVAL_JAIL_DIR . $this->task_id . '.ok')) {
                    return JobResult::SystemError();
                }
            }

            if ($this->has_unique_output) {
                log_error("Nu stiu ce sa fac cu output unic");
                return JobResult::SystemError();
            } else {
                // Custom grader.
                if (!@copy(IA_EVAL_TEMP_DIR . 'eval', IA_EVAL_JAIL_DIR . 'eval')) {
                    log_warn("Failed copying custom grader");
                    return JobResult::SystemError();
                }
                @system("chmod a+x eval", $res);
                if ($res) {
                    log_warn("Failed to chmod a+x custom grader");
                    return JobResult::SystemError();
                }

                $jrunres = jail_run('eval', 1000, 64000, true);
                log_print("JRUN grader: ".$jrunres['result'].": ".$jrunres['message']);
                if ($jrunres['result'] != 'OK') {
                    log_warn("Failed running grader!");
                    return JobResult::SystemError();
                }

                $jrunres['stdout'] = trim($jrunres['stdout']);
                $score = (int)$jrunres['stdout'];
                if ((string)$score !== $jrunres['stdout']) {
                    log_warn("Grader didn't return a score in stdout");
                    return JobResult::SystemError();
                }

                $message = $jrunres['stderr'];
                $message = preg_replace("/\s*\.?\n?^/i", "", $message);
                if (strpos("\n", $message) || strlen($message) > 100) {
                    log_warn("Grader returned a malformed message");
                    return JobResult::SystemError();
                }

                log_print("Grader gave $score points and said $message");

                // FIXME: Run grader here.
                $score = 100 / $this->test_count;
                $result->score += $score;
                $result->log .= "$message: $score puncte";
            }

            log_print("");
        }

        $result->log .= "\n\nPunctaj total: {$result->score}\n";

        return $result;
    }
}

?>
