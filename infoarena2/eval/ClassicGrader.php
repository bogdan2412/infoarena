<?php

class ClassicGrader {
    public $task_id;

    public $test_count;

    public $time_limit;

    public $has_ok_files;

    public $unique_output;

    public $evaluator;

    function __construct($id, $parameters) {
        assert(is_array($parameters));

        $this->task_id = $id;
        $this->evaluator = (string)$parameters['evaluator'];
        $this->test_count = (int)$parameters['tests'];
        $this->time_limit = (int)$parameters['timelimit'];
        $this->unique_output = (bool)$parameters['unique_output'];
        $this->has_ok_files = (bool)$parameters['okfiles'];
    }

    function handle_test($testno)
    {
        // Copiem input. 
    }

    function Grade($file_contents, $file_extension) {
        $result = new JobResult();
        $result->score = 0;

        // Clean jail and temp
        if (!clean_dir(IA_EVAL_TEMP_DIR)) {
            return JobResult::SystemError();
        }

        // chdir to temp dir.
        if (!chdir(IA_EVAL_TEMP_DIR)) {
            tprint("Can't chdir to temp dir.");
            return JobResult::SystemError();
        }

        // Compile custom evaluator.
        if (!$this->unique_output) {
            if (!copy(IA_GRADER_DIR . $this->task_id . '/' . $this->evaluator,
                        IA_EVAL_TEMP_DIR . $this->evaluator)) {
                tprint("Can't move evaluator source to temp dir");
                return JobResult::SystemError();
            }

            if (!compile_file($this->evaluator , $compiler_messages)) {
                tprint("Can't compiler evaluator");
                return JobResult::SystemError();
            }
        }

        // Compile user source.
        if (!file_put_contents("user." . $file_extension, $file_contents)) {
            tprint("Can't write user file on disk");
            return JobResult::SystemError();
        }
        if (!compile_file("user." . $file_extension, $compiler_messages)) {
            if ($compiler_messages === false) {
                return JobResult::SystemError();
            }
            $result->message = "Eroare de compilare";
            $result->log = "Eroare de compilare:\n" . $compiler_messages;
        } else {
            $result->log = "Compilare:\n" . $compiler_messages;
        }

        for ($testno = 1; $testno < $this->test_count; ++$testno) {
            if (!chdir(IA_EVAL_DIR)) {
                tprint("Can't chdir to eval dir.");
                return JobResult::SystemError();
            }
            if (!clean_dir(IA_EVAL_JAIL_DIR)) {
                return JobResult::SystemError();
            }
            if (!chdir(IA_EVAL_JAIL_DIR)) {
                tprint("Can't chdir to jail dir.");
                return JobResult::SystemError();
            }

            if (!copy(IA_GRADER_DIR . $this->task_id . '/test' . $testno . '.in',
                        IA_EVAL_JAIL_DIR . $this->task_id . '.in')) {
                tprint("Failed copying test $testno");
                return JobResult::SystemError();
            }

            if (!copy(IA_EVAL_TEMP_DIR . 'user', IA_EVAL_JAIL_DIR . 'user')) {
                tprint("Failed copying test $testno");
                return JobResult::SystemError();
            }

            @system("chmod a+x user", $res);
            if ($res) {
                tprint("Failed to chmod a+x user program");
                return JobResult::SystemError();
            }
            
            $time = $this->time_limit * 1000;
            $memory = 640000;
            $jrun_msg = jail_run('user', $time, $memory);
        }

        return $result;
    }
}

?>
