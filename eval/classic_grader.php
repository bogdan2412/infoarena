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

    // Clean temp dir and chdir
    $ret = clean_dir(IA_ROOT_DIR.'eval/temp/');
    log_assert($ret, "Can't clean to temp dir.");
    $ret = chdir(IA_ROOT_DIR.'eval/temp/');
    log_assert($ret, "Can't chdir to temp dir.");

    // Compile custom evaluator.
    // Don't send system error, send custom message
    if ($tparams['evaluator'] !== '') {
        if (!copy_grader_file($task, $tparams['evaluator'],
                IA_ROOT_DIR.'eval/temp/'.$tparams['evaluator'])) {
            log_print('Task eval not found');
            $result['message'] = 'Eroare in setarile problemei';
            $result['log'] = "Lipseste evaluatorul problemei.\n";
            $result['log'] .= "Ar trebui sa existe un atasament".
                    " 'grader_{$tparams['evaluator']}' ".
                    "la pagina cu enuntul problemei";
            return $result;
        }

        if (!compile_file($tparams['evaluator'], 'eval', $compiler_messages)) {
            log_print('Task eval compile error');
            $result['message'] = 'Eroare de compilare in evaluator';
            $result['log'] = "Eroare de compilare:\n" . $compiler_messages;
            return $result;
        }
    } else {
        //log_print("Unique output, no evaluator!");
    }

    // Compile user source.
    $ret = @file_put_contents("user." . $job['compiler_id'], $job['file_contents']);
    //log_assert($ret, "Failed putting user file on disk");
    if (!compile_file("user." . $job['compiler_id'], 'user', $compiler_messages)) {
        log_print('User program compile error');
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

        // Clean and chdir to jail dir.
        $ret = clean_dir($jaildir);
        log_assert($ret, "Can't clean jail dir.");
        $ret = chdir($jaildir);
        log_assert($ret, "Can't chdir to jail dir.");

        // Download grader file.
        if (!copy_grader_file($task, 'test' . $testno . '.in', $infile)) {
            log_print("Test $testno: input not found");
            $result['message'] = 'Eroare in teste';
            $result['score'] = 0;
            $result['log'] = "Lipseste intrarea testului $testno.\n";
            $result['log'] .= "Ar trebui sa existe un atasament".
                    " 'grader_test$testno.in' ".
                    "la pagina cu enuntul problemei";
            return $result;
        }

        $ret = copy(IA_ROOT_DIR.'eval/temp/user', $jaildir . $userfile);
        log_assert($ret, "Failed copying user program");
        $ret = chmod($userfile, 0555);
        log_assert("Failed to chmod a+x user program");
     
        // Run user program.
        $jrunres = jail_run($userfile, $jaildir, $tparams['timelimit'] * 1000, $tparams['memlimit']);
        log_assert($jrunres['result'] != 'ERROR', "Error in jrun.");
        if ($jrunres['result'] == 'FAIL') {
            log_print("Test $testno: User program failed: {$jrunres['message']} ".
                        "{$jrunres['time']}ms {$jrunres['memory']}kb");
            $result['log'] .= "eroare: timp {$jrunres['time']}ms: mem {$jrunres['memory']}kb: {$jrunres['message']}: 0 puncte";
            // User program failed on this test. Bye bye.
            continue;
        } else {
            $result['log'] .= "ok: timp {$jrunres['time']}ms: mem {$jrunres['memory']}kb: ";
        }

        // Copy ok file, if used.
        if ($tparams['okfiles']) {
            if (!copy_grader_file($task , 'test' . $testno . '.ok', $okfile)) {
                log_print("Test $testno: .ok file not found");
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
                    log_print("Test $testno: Diff eval: Files identical"); 
                    $score = 100 / $tparams['tests'];
                    $result['score'] += $score;
                    $result['log'] .= "OK: $score puncte";
                } else {
                    log_print("Test $testno: Diff eval: Files differ"); 
                    $result['log'] .= "Incorect: 0 puncte";
                }
            } else {
                log_print("Test $testno: Diff eval: output missing"); 
                $result['log'] .= "Fisier de iesire lipsa: 0 puncte";
            }
        } else {
            // Custom grader.
            $ret = copy(IA_ROOT_DIR . 'eval/temp/eval', $jaildir . 'eval');
            log_assert($ret, "Failed copying custom grader");
            $ret = chmod('eval', 0555);
            log_assert("Failed to chmod a+x user program");

            // Run task eval, and check result
            $jrunres = jail_run('eval', $jaildir,
                    IA_JUDGE_TASK_EVAL_TIMELIMIT,
                    IA_JUDGE_TASK_EVAL_MEMLIMIT,
                    true);
            log_assert($jrunres['result'] != 'ERROR', "Error in jrun");

            // Task eval is not allowed to fail.
            if ($jrunres['result'] == 'FAIL') {
                log_print("Test $testno: Task eval failed");
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
                $score = 0;
            } else if (!is_whole_number($jrunres['stdout'])) {
                log_print("Test $testno: Task eval score broken");
                $result['message'] = 'Eroare in evaluator';
                $result['score'] = 0;
                $result['log'] = "Evaluatorul nu a returnat un numar la stdout ".
                        "pe testul $testno (se ignora spatii, newline, etc)";
                return $result;
            } else {
                $score = (int)$jrunres['stdout'];
            }

            // Get message.
            $message = $jrunres['stderr'];
            if (!preg_match("/^([^\n]*)$/i", $message, $match)) {
                log_error("Broken preg_match???");
            }
            $message = $match[1];
            if (strpos("\n", $message) || strlen($message) > 100) {
                log_print("Test $testno: Task eval message broken");
                $result['message'] = 'Eroare in evaluator';
                $result['score'] = 0;
                $result['log'] = "Evaluatorul a returnat un mesaj mai lung de 100 ".
                        "de caractere la stdout in testul $testno";
                return $result;
            }

            // log.
            log_print("Test $testno: Eval gave $score points and said $message");
            $result['score'] += $score;
            $result['log'] .= "$message: $score puncte";
        }
    }

    $result['message'] = 'Evaluare completa';
    $result['log'] .= "\n\nPunctaj total: {$result['score']}\n";

    return $result;
}

?>
