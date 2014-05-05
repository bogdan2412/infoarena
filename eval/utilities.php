<?php

// Sleeps for a number of miliseconds.
function milisleep($ms) {
    usleep($ms * 1000);
}

// Delete and remake a directory.
// Return success value.
function clean_dir($dir) {
    system("rm -rf " . $dir);
    if (@mkdir($dir, 0777, true) === false) {
        log_warn("Failed re-creating directory $dir");
        return false;
    }
    if (@chmod($dir, 0777) == false) {
        log_warn("Failed chmod 0777 directory $dir");
        return false;
    }
    return true;
}

function eval_assert($condition, $message = 'Assertion Error') {
    if (!$condition) {
        log_print('Evaluator assertion failed: ' . $message);
        throw new EvalSystemError($message);
    }
}

// Compile a certain file.
// Returns success value, and a friendly error message in $compiler_message.
//
// Can currently handle C, C++, FreePascal, and Python.
function compile_file($input_file_name, $output_file_name, &$compiler_message) {
    $compiler_message = false;
    $compiler_lines = array(
            // Make sure -lm stays after source file & target output
            'c' => 'gcc -DINFOARENA -Wall -O2 -static -std=c11 ' .
                   '%file_name% -o %exe_name% -lm',
            'cpp' => 'g++ -DINFOARENA -Wall -O2 -static -std=c++0x ' .
                     '%file_name% -o %exe_name% -lm',
            'pas' => 'fpc -O2 -Xs %file_name% -dINFOARENA',
            'fpc' => 'fpc -O2 -Xs %file_name% -dINFOARENA',
            'py' => IA_JUDGE_PY_COMPILER.' %file_name% %exe_name%',
            'java' => 'javac %file_name%'
    );
    $matches = array();
    if (!preg_match("/^(.*)\.(c|cpp|pas|fpc|py|java)$/i",
                    $input_file_name, $matches)) {
        $compiler_message = "Nu am putut sa determin compilatorul ".
                "pentru '$input_file_name'.";
        return false;
    }
    $exe_name = $matches[1];
    $extension = $matches[2];
    if (!isset($compiler_lines[$extension])) {
        $compiler_message = "Nu stiu cum sa compilez fisiere '$extension'";
        return false;
    }

    $cmdline = $compiler_lines[$extension];
    $cmdline = preg_replace('/%file_name%/', $input_file_name, $cmdline);
    $cmdline = preg_replace('/%exe_name%/', $exe_name, $cmdline);

    // Running compiler
    $compiler_message = array();
    $return_status = 1;
    exec("$cmdline 2>&1", $compiler_message, $return_status);

    $compiler_message = array_slice($compiler_message, 0, 50);
    $compiler_message = implode("\n", $compiler_message);

    if ($extension == "java")
        return $return_status == 0;

    // This is the BEST way to fail on compilation errors.
    if (!is_executable($exe_name)) {
        return false;
    }

    // Rename to $output_file_name.
    if ($exe_name != $output_file_name) {
        eval_assert(@rename($exe_name, $output_file_name),
                    "Failed renaming $exe_name to $output_file_name");
    }

    // Hooray!
    return true;
}

// Parses jrun output.
// Returns an array with result, time, memory and message.
//
// Result is 'OK', 'FAIL' or 'ERROR'
// If result is ERROR time and memory are not available
// Returns false on error.
function jrun_parse_message($message) {
    $matches = array();
    if (!preg_match("/^(ERROR|FAIL|OK):\ (.*)$/", $message, $matches)) {
        log_warn("Invalid jrun output: $message");
        return false;
    }

    $res = array();
    $res['result'] = $matches[1];
    $res['message'] = $matches[2];
    if ($matches[1] == 'OK' || $matches[1] == 'FAIL') {
        if (!preg_match("/^time\ ([0-9]+)ms\ memory\ ([0-9]+)kb:\ (.*)$/",
                    $res['message'], $matches)) {
            return false;
        } else {
            $res['time'] = (int)$matches[1];
            $res['memory'] = (int)$matches[2];
            $res['message'] = $matches[3];
        }
    }

    // Trim . .\n and other stupid shit like that.
    $res['message'] = preg_replace("/\s*\.?\n?^/i", "", $res['message']);
    return $res;
}

// Returns a jrun message array for an error.
// Sort of a hack.
function jrun_make_error($message) {
    return array('result' => "ERROR", 'message' => $message);
}

// Run a program in a special jail environment.
// It calls an external jailer and parses output.
//
// $time and $memory contain the time and memory limits (or false).
// if $capture_std is true the it will ask jrun to capture user program
// stdin/stdout.
//
// The return value is an array:
//      result: OK:    program ran perfectly
//              FAIL:  program failed for various reasons.
//              ERROR: internal error (user program not to blame).
//      message: an explanatory string.
//      time, memory: Amount of time and memory the program used.
//      stdin, stderr: Contents of user program standard i/o.
//               Only if $capture_std is true.
//
// All timings are in miliseconds and memory is in kilobytes
//
// If result is ERROR time, memory, stdin and stdout are never set.
function jail_run($program, $jaildir, $time, $memory, $capture_std = false,
                  $redirect_std = array(), $async = false,
                  $extra_args = '') {
    eval_assert(is_whole_number($time));
    eval_assert(is_whole_number($memory));
    eval_assert(is_array($redirect_std));
    eval_assert(!$capture_std || !$redirect_std,
                'Can not have $capture_std and $redirect_std at the same time');

    $cmdline = IA_ROOT_DIR . 'jrun/jrun';
    $cmdline .= " --prog=./" . $program;
    $cmdline .= " --dir=" . $jaildir;
    $cmdline .= " --chroot";
    $cmdline .= " --block-syscalls-file=" . IA_ROOT_DIR . 'jrun/bad_syscalls';
    if (defined('IA_JUDGE_JRUN_NICE') && IA_JUDGE_JRUN_NICE != 0) {
        $cmdline .= " --nice=" . IA_JUDGE_JRUN_NICE;
    }
    if (defined('IA_JUDGE_JRUN_UID')) {
        $cmdline .= " --uid=" . IA_JUDGE_JRUN_UID;
    }
    if (defined('IA_JUDGE_JRUN_GID')) {
        $cmdline .= " --gid=" . IA_JUDGE_JRUN_GID;
    }
    if ($capture_std) {
        $cmdline .= " --redirect-stdout=jailed_stdout";
        $cmdline .= " --redirect-stderr=jailed_stderr";
    }
    if ($redirect_std) {
        foreach (array('in', 'out', 'err') as $file) {
            if (array_key_exists($file, $redirect_std)) {
                $cmdline .= ' --redirect-std' . $file . '=' .
                    escapeshellarg($redirect_std[$file]);
            }
        }
        if (getattr($redirect_std, 'out-before-in')) {
            $cmdline .= ' --redirect-out-before-in';
        }
    }
    if (isset($time)) {
        $cmdline .= " --time-limit=" . $time;
    }
    if (isset($memory)) {
        $cmdline .= " --memory-limit=" . $memory;
    }
    $cmdline .= ' ' . $extra_args;
    // $cmdline .= " --verbose";

    log_print("Running $cmdline");
    $pipes = array();
    $process = proc_open($cmdline,
                         array(1 => array('pipe', 'w'),
                               2 => array('pipe', 'w')),
                         $pipes);

    $jrun_process = array(
        'process' => $process,
        'pipes' => $pipes,
        'jaildir' => $jaildir,
        'time' => $time,
        'memory' => $memory,
        'capture_std' => $capture_std,
    );

    if ($async) {
        return $jrun_process;
    }

    return jrun_get_result($jrun_process);
}

function jail_run_java($jaildir, $time, $memory, $permitted_files = array()) {
    eval_assert(is_whole_number($time));
    eval_assert(is_whole_number($memory));
    eval_assert(is_array($permitted_files));
    $cmdline = "java -Xmx512m -Xss128m -DONLINE_JUDGE=true -Duser.language=en";
    $cmdline .= " -Duser.region=US -Duser.variant=US -jar";
    $cmdline .= " ". IA_ROOT_DIR . "jrun/java-sandbox/InfoarenaJudge.jar";
    $cmdline .= " ". IA_ROOT_DIR . "jrun/java-sandbox/InfoarenaJudge.so";
    $cmdline .= " ".$time;
    $cmdline .= " ".$memory;
    $cmdline .= " ".IA_JUDGE_JRUN_UID;
    $cmdline .= " ".IA_JUDGE_JRUN_GID;
    foreach ($permitted_files as $file) {
        $cmdline .= " ".escapeshellarg($file);
    }

    log_print("Running $cmdline");
    $pipes = array();
    $process = proc_open($cmdline,
                         array(1 => array('pipe', 'w'),
                               2 => array('pipe', 'w')),
                         $pipes);

    $jrun_process = array(
        'process' => $process,
        'pipes' => $pipes,
        'jaildir' => $jaildir,
        'time' => $time,
        'memory' => $memory,
        'capture_std' => false
    );

    return jrun_get_result($jrun_process);
}

/**
 * Receives an array returned by an async jail_run call containing information
 * about a running process, its pipe file descriptors and other jail_run
 * parameters, waits for the process to terminate and returns jrun's result.
 *
 * @param   array   $jrun_process
 * @returns string
 */
function jrun_get_result($jrun_process) {
    $results = jrun_get_result_many(array($jrun_process));
    return array_pop($results);
}

/**
 * This method is called by jrun_get_result to check whether or not a
 * process is ready to terminate.
 *
 * @param   array    $jrun_process
 * @returns boolean                 Whether or not the process is still alive
 */
function jrun_check_process_alive(&$jrun_process) {
    if (!$jrun_process['alive']) {
        return false;
    }

    $proc_status = proc_get_status($jrun_process['process']);
    eval_assert($proc_status !== false, 'proc_get_status() call failed');
    if (!$proc_status['running']) {
        // Fetch final data left in pipes.
        $jrun_process['stdout'] .=
            stream_get_contents($jrun_process['pipes'][1]);
        $jrun_process['stderr'] .=
            stream_get_contents($jrun_process['pipes'][2]);
        proc_close($jrun_process['process']);
        $jrun_process['alive'] = false;
        return false;
    }
    return true;
}

/**
 * Receives an array of one or more jrun processes returned by async jail_run
 * calls, waits for all of them to terminate and returns their corresponding
 * jrun results.
 *
 * @param   array   $jrun_processes
 * @returns array
 */
function jrun_get_result_many($jrun_processes) {
    $jrun_processes = array_values($jrun_processes);
    $still_alive = count($jrun_processes);
    foreach ($jrun_processes as $id => &$jrun_process) {
        $jrun_process['alive'] = true;
        $jrun_process['stdout'] = '';
        $jrun_process['stderr'] = '';
    }

    // Need to fetch data in parallel from each process' pipes, since they may
    // fill up and block process execution otherwise.
    while ($still_alive > 0) {
        // Build arrays for stream_select() call
        $read = array();
        $write = null;
        $except = null;
        foreach ($jrun_processes as $id => &$jrun_process) {
            if (!$jrun_process['alive']) {
                continue;
            }
            $read[$id * 2 + 0] = $jrun_process['pipes'][1];
            $read[$id * 2 + 1] = $jrun_process['pipes'][2];
        }

        // Wait for data to become ready on any pipe
        $nready = stream_select($read, $write, $except, 2);
        eval_assert($nready !== false, 'stream_select() call failed');

        if ($nready === 0) {
            // No data is available after timeout, check if processes still
            // alive.
            foreach ($jrun_processes as &$jrun_process) {
                if ($jrun_process['alive'] &&
                    !jrun_check_process_alive($jrun_process)) {
                    $still_alive -= 1;
                }
            }
        } else {
            // Fetch data from available pipes.
            foreach ($read as $stream_id => $stream) {
                $jrun_process = &$jrun_processes[$stream_id / 2];
                if (!$jrun_process['alive']) {
                    // If process has terminated in the mean time, then it
                    // would have been processed earlier in the loop
                    continue;
                }

                if ($stream_id % 2 == 0) {
                    $jrun_process['stdout'] .= fread($stream, 65536);
                } else {
                    $jrun_process['stderr'] .= fread($stream, 65536);
                }

                if (!jrun_check_process_alive($jrun_process)) {
                    $still_alive -= 1;
                }
            }
        }
    }

    $results = array();
    foreach ($jrun_processes as &$jrun_process) {
        $message = $jrun_process['stderr'] . $jrun_process['stdout'];
        // Get the last line from the message
        $message = strrev(strtok(strrev($message), "\n"));

        $result = jrun_parse_message($message);
        if ($result == false) {
            return jrun_make_error('Failed executing jail');
        }

        if ($jrun_process['capture_std']) {
            $jaildir = $jrun_process['jaildir'];
            $result['stdout'] = @file_get_contents($jaildir.'jailed_stdout');
            if ($result['stdout'] === false) {
                return jrun_make_error('Failed reading captured stdout');
            }
            $result['stderr'] = @file_get_contents($jaildir.'jailed_stderr');
            if ($result['stderr'] === false) {
                return jrun_make_error('Failed reading captured stderr');
            }
        }

        if ($result['result'] == 'OK') {
            eval_assert($result['time'] <= $jrun_process['time'] &&
                        $result['memory'] <= $jrun_process['memory'],
                        'JRun returns OK, but time or memory limit is broken');
        }

        $results[] = $result;
    }
    return $results;
}
