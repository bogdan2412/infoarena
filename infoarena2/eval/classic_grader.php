<?php

// Grades a classic task.
function task_grade_job_classic($task, $tparams, $job) {
    $result = array(
            'score' => 0,
            'message' => 'Evaluare incompleta',
            'log' => ''
    );
    log_assert_valid(task_validate($task));
    log_assert_valid(task_validate_parameters($task['type'], $tparams));

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

        $userfile = "user_{$job['id']}_{$testno}";

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

        if (!@copy(IA_EVAL_TEMP_DIR . 'user', IA_EVAL_JAIL_DIR . $userfile)) {
            log_warn("Failed copying user program");
            return jobresult_system_error();
        }
        @system("chmod a+x $userfile", $res);
        if ($res) {
            log_warn("Failed to chmod a+x user program");
            return jobresult_system_error();
        }
     
        // Run user program.
        $jrunres = jail_run($userfile, $tparams['timelimit'] * 1000, $tparams['memlimit']);
        log_print("JRUN user: ".$jrunres['result'].": ".$jrunres['message']);
        if ($jrunres['result'] == 'ERROR') {
            return jobresult_system_error();
        } else if ($jrunres['result'] == 'FAIL') {
            $result['log'] .= "eroare: timp {$jrunres['time']}ms: mem {$jrunres['memory']}kb: {$jrunres['message']}: 0 puncte";
            log_print("");
            continue;
        } else {
            $result['log'] .= "ok: timp {$jrunres['time']}ms: mem {$jrunres['memory']}kb: ";
        }

        // Copy ok file, if used.
        if ($tparams['okfiles']) {
            if (!copy_grader_file($task , 'test' . $testno . '.ok', $okfile)) {
                return jobresult_system_error();
            }
        }

        if ($tparams['unique_output']) {
            // Diff grading, trivial.
            if (is_readable($outfile)) {
                $diff_output = shell_exec("diff -qBbEa $outfile $okfile");
                if ($diff_output == '') {
                    log_print("output and okfile match");
                    $score = 100 / $tparams['tests'];
                    $result['score'] += $score;
                    $result['log'] .= "OK: $score puncte";
                } else {
                    log_print("output and okfile don't match");
                    //log_print(file_get_contents($outfile)." != ".file_get_contents($okfile));
                    $result['log'] .= "Incorect: 0 puncte";
                }
            } else {
                $result['log'] .= "Fisier de iesire lipsa: 0 puncte";
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

            // Run grader.
            $jrunres = jail_run('eval',
                IA_EVAL_TASK_GRADER_TIMELIMIT,
                IA_EVAL_TASK_GRADER_MEMLIMIT,
                true);
            log_print("JRUN grader: ".$jrunres['result'].": ".$jrunres['message']);
            if ($jrunres['result'] != 'OK') {
                log_warn("Failed running grader!");
                return jobresult_system_error();
            }

            // Get score.
            $jrunres['stdout'] = trim($jrunres['stdout']);
            if ($jrunres['stdout'] === '') {
                log_warn("Grader didn't return stdout, assuming 0 points");
            } else if (!is_whole_number($jrunres['stdout'])) {
                log_warn("Grader didn't return number in stdout.");
                return jobresult_system_error();
            }
            $score = (int)$jrunres['stdout'];

            // Get message.
            $message = $jrunres['stderr'];
            if (!preg_match("/^([^\n]*)$/i", $message, $match)) {
                log_error("Broken grader message?");
            }
            $message = $match[1];
            if (strpos("\n", $message) || strlen($message) > 100) {
                log_warn("Grader returned a malformed message");
                return jobresult_system_error();
            }

            // log.
            log_print("Grader gave $score points and said $message");
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
