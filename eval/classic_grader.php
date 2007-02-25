<?php

// Grades a classic task.
function classic_task_grade_job($task, $tparams, $job) {
    $result = array(
            'score' => 0,
            'message' => 'Evaluare incompleta',
            'log' => ''
    );
    log_assert_valid(task_validate($task));
    log_assert_valid(task_validate_parameters($task['type'], $tparams));

    // Clean temp dir
    if (!clean_dir(IA_ROOT_DIR.'eval/temp/')) {
        log_warn("Can't clean to temp dir.");
        return jobresult_system_error();
    }

    // chdir to temp dir.
    if (!@chdir(IA_ROOT_DIR.'eval/temp/')) {
        log_warn("Can't chdir to temp dir.");
        return jobresult_system_error();
    }

    // Compile custom evaluator.
    // Don't send system error, send custom message
    if ($tparams['evaluator'] !== '') {
        if (!copy_grader_file($task, $tparams['evaluator'],
                IA_ROOT_DIR.'eval/temp/'.$tparams['evaluator'])) {
            $result['message'] = 'Eroare in setarile problemei';
            $result['log'] = "Lipseste evaluatorul problemei.\n";
            $result['log'] .= "Ar trebui sa existe un atasament".
                    " 'grader_{$tparams['evaluator']}' ".
                    "la pagina cu enuntul problemei";
            return $result;
        }

        if (!compile_file($tparams['evaluator'], 'eval', $compiler_messages)) {
            $result['message'] = 'Eroare de compilare in evaluator';
            $result['log'] = "Eroare de compilare:\n" . $compiler_messages;
            return $result;
        }
    } else {
        log_print("Unique output, no evaluator!");
    }

    // Compile user source.
    if (false === @file_put_contents("user." . $job['compiler_id'], $job['file_contents'])) {
        log_warn("Can't write user file on disk.");
        return jobresult_system_error();
    }
    if (!compile_file("user." . $job['compiler_id'], 'user', $compiler_messages)) {
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
        $jaildir = IA_ROOT_DIR . 'eval/jail/';

        $infile = $jaildir.$task['id'].'.in';
        $outfile = $jaildir.$task['id'].'.out';
        $okfile = $jaildir.$task['id'].'.ok';

        $userfile = "user_{$job['id']}_{$testno}";

        if (!@chdir(IA_ROOT_DIR.'eval/temp/')) {
            log_warn("Can't chdir to ia root dir.");
            return jobresult_system_error();
        }
        if (!clean_dir($jaildir)) {
            return jobresult_system_error();
        }
        if (!@chdir($jaildir)) {
            log_warn("Can't chdir to jail dir.");
            return jobresult_system_error();
        }

        if (!copy_grader_file($task, 'test' . $testno . '.in', $infile)) {
            $result['message'] = 'Eroare in teste';
            $result['score'] = 0;
            $result['log'] = "Lipseste intrarea testul $testno.\n";
            $result['log'] .= "Ar trebui sa existe un atasament".
                    " 'grader_test$testno.in' ".
                    "la pagina cu enuntul problemei";
            return $result;
        }

        if (!@copy(IA_ROOT_DIR.'eval/temp/user', $jaildir . $userfile)) {
            log_warn("Failed copying user program");
            return jobresult_system_error();
        }
        @system("chmod a+x $userfile", $res);
        if ($res) {
            log_warn("Failed to chmod a+x user program");
            return jobresult_system_error();
        }
     
        // Run user program.
        $jrunres = jail_run($userfile, $jaildir, $tparams['timelimit'] * 1000, $tparams['memlimit']);
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
                $result['message'] = 'Eroare in teste';
                $result['score'] = 0;
                $result['log'] = "Lipseste fisierul .ok al testului $testno.\n";
                $result['log'] .= "Ar trebui sa existe un atasament".
                        " 'grader_test$testno.ok' ".
                        "la pagina cu enuntul problemei";
                return $result;
            }
        }

        if ($tparams['evaluator'] === '') {
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
            if (!@copy(IA_ROOT_DIR . 'eval/temp/eval', $jaildir . 'eval')) {
                log_warn("Failed copying custom grader");
                return jobresult_system_error();
            }
            @system("chmod a+x eval", $res);
            if ($res) {
                log_warn("Failed to chmod a+x custom grader");
                return jobresult_system_error();
            }

            // Run grader, and check result
            $jrunres = jail_run('eval', $jaildir,
                IA_JUDGE_TASK_EVAL_TIMELIMIT,
                IA_JUDGE_TASK_EVAL_MEMLIMIT,
                true);
            log_print("JRUN grader: ".$jrunres['result'].": ".$jrunres['message']);
            if ($jrunres['result'] == 'ERROR') {
                return jobresult_system_error();
            } else if ($jrunres['result'] == 'FAIL') {
                $result['message'] = 'Eroare in evaluator';
                $result['score'] = 0;
                $result['log'] = "A aparut o eroare in rularea evaluatorului ".
                        "pe testul $testno: {$jrunres['message']}".
                        ": timp {$jrunres['time']}ms".
                        ": mem {$jrunres['memory']}kb";
                return $result;
            }

            // Get score.
            $jrunres['stdout'] = trim($jrunres['stdout']);
            if ($jrunres['stdout'] === '') {
                log_warn("Grader didn't return stdout, assuming 0 points");
            } else if (!is_whole_number($jrunres['stdout'])) {
                $result['message'] = 'Eroare in evaluator';
                $result['score'] = 0;
                $result['log'] = "Evaluatorul nu a returnat un numar la stdout ".
                        "pe testul $testno (se ignora spatii, newline, etc)";
                return $result;
            }
            $score = (int)$jrunres['stdout'];

            // Get message.
            $message = $jrunres['stderr'];
            if (!preg_match("/^([^\n]*)$/i", $message, $match)) {
                log_error("Broken grader message?");
            }
            $message = $match[1];
            if (strpos("\n", $message) || strlen($message) > 100) {
                $result['message'] = 'Eroare in evaluator';
                $result['score'] = 0;
                $result['log'] = "Evaluatorul a returnat un mesaj mai lung de 100 ".
                        "de caractere la stdout in testul $testno";
                return $result;
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
