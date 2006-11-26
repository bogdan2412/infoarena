<?php

// Grades a classic task.
function task_grade_job_classic($task, $tparams, $job) {
    $result = array(
            'score' => 0,
            'message' => 'Evaluare incompleta',
            'log' => ''
    );

    // Clean jail and temp
    if (!clean_dir(IA_EVAL_TEMP_DIR)) {
        log_warn("Can't clean to temp dir.");
        return jobresult_system_error();
    }

    // chdir to temp dir.
    if (!@chdir(IA_EVAL_TEMP_DIR)) {
        log_warn("Can't chdir to temp dir.");
        return jobresult_system_error();
    }

    // Compile custom evaluator.
    if (!$tparams['unique_output']) {
        if (!copy_grader_file($task, $tparams['evaluator'],
                IA_EVAL_TEMP_DIR . $tparams['evaluator'])) {
            return jobresult_system_error();
        }

        if (!compile_file($tparams['evaluator'] , $compiler_messages)) {
            log_warn("Can't compile evaluator.");
            return jobresult_system_error();
        }
    } else {
        log_print("Unique output, no evaluator!");
    }

    // Compile user source.
    if (!@file_put_contents("user." . $job['compiler_id'], $job['file_contents'])) {
        log_warn("Can't write user file on disk.");
        return jobresult_system_error();
    }
    if (!compile_file("user." . $job['compiler_id'], $compiler_messages)) {
        if ($compiler_messages === false) {
            return jobresult_system_error();
        }
        $result['message'] = "Eroare de compilare";
        $result['log'] = "Eroare de compilare:\n" . $compiler_messages;
        return $result;
    } else {
        $result['log'] = "Compilare:\n" . $compiler_messages . "\n";
    }

    // Running tests.
    for ($testno = 1; $testno <= $tparams['tests']; ++$testno) {
        $result['log'] .= "\nRulez testul $testno: ";

        $infile = IA_EVAL_JAIL_DIR . $task['id'] . '.in';
        $outfile = IA_EVAL_JAIL_DIR . $task['id'] . '.out';
        $okfile = IA_EVAL_JAIL_DIR . $task['id'] . '.ok';

        if (!@chdir(IA_EVAL_DIR)) {
            log_warn("Can't chdir to eval dir.");
            return jobresult_system_error();
        }
        if (!clean_dir(IA_EVAL_JAIL_DIR)) {
            return jobresult_system_error();
        }
        if (!@chdir(IA_EVAL_JAIL_DIR)) {
            log_warn("Can't chdir to jail dir.");
            return jobresult_system_error();
        }

        if (!copy_grader_file($task, 'test' . $testno . '.in', $infile)) {
            return jobresult_system_error();
        }

        if (!@copy(IA_EVAL_TEMP_DIR . 'user', IA_EVAL_JAIL_DIR . 'user')) {
            log_warn("Failed copying user program");
            return jobresult_system_error();
        }
        @system("chmod a+x user", $res);
        if ($res) {
            log_warn("Failed to chmod a+x user program");
            return jobresult_system_error();
        }
     
        // Run user program.
        $jrunres = jail_run('user', $tparams['time_limit'] * 1000, $tparams['memory_limit']);
        log_print("JRUN user: ".$jrunres['result'].": ".$jrunres['message']);
        if ($jrunres['result'] == 'ERROR') {
            return jobresult_system_error();
        } else if ($jrunres['result'] == 'FAIL') {
            $result['log'] = "eroare: ".$jrunres['message'].": 0 puncte";
            log_print("");
            continue;
        } else {
            $result['log'] .= "ok: timp ".$jrunres['time']."ms ".
                    $jrunres['memory']."kb: ";
        }

        // Copy ok file, if used.
        if ($tparams['ok_files']) {
            if (!copy_grader_file($task , 'test' . $testno . '.ok', $okfile)) {
                return jobresult_system_error();
            }
        }

        if ($tparams['unique_output']) {
            if (!is_readable($outfile)) {
                $result['log'] += "Fisier de iesire lipsa: 0 puncte";
            }
            @system("diff -BbEa $infile $outfile &> /dev/null", $res);
            if ($res) {
                $score = 100 / $tparams['tests'];
                $result['score'] += $score;
                $result['log'] += "OK: $score puncte";
            } else {
                $result['log'] += "Incorect: 0 puncte";
            }
        } else {
            // Custom grader.
            if (!@copy(IA_EVAL_TEMP_DIR . 'eval', IA_EVAL_JAIL_DIR . 'eval')) {
                log_warn("Failed copying custom grader");
                return jobresult_system_error();
            }
            @system("chmod a+x eval", $res);
            if ($res) {
                log_warn("Failed to chmod a+x custom grader");
                return jobresult_system_error();
            }

            $jrunres = jail_run('eval', 1000, 64000, true);
            log_print("JRUN grader: ".$jrunres['result'].": ".$jrunres['message']);
            if ($jrunres['result'] != 'OK') {
                log_warn("Failed running grader!");
                return jobresult_system_error();
            }

            $jrunres['stdout'] = trim($jrunres['stdout']);
            $score = (int)$jrunres['stdout'];
            if ((string)$score !== $jrunres['stdout']) {
                log_warn("Grader didn't return a score in stdout");
                return jobresult_system_error();
            }

            $message = $jrunres['stderr'];
            $message = preg_replace("/\s*\.?\n?^/i", "", $message);
            if (strpos("\n", $message) || strlen($message) > 100) {
                log_warn("Grader returned a malformed message");
                return jobresult_system_error();
            }

            log_print("Grader gave $score points and said $message");

            // FIXME: Run grader here.
            $result['score'] += $score;
            $result['log'] .= "$message: $score puncte";
        }

        log_print("");
    }

    $result['message'] = 'Evaluare completa';
    $result['log'] .= "\n\nPunctaj total: {$result['score']}\n";

    return $result;
}

?>
