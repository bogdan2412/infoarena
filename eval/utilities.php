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
            'c' => 'gcc -Wall -O2 -static %file_name% -o %exe_name% -lm',
            'cpp' => 'g++ -Wall -O2 -static %file_name% -o %exe_name% -lm',
            'pas' => 'fpc -O2 -Xs %file_name%',
            'fpc' => 'fpc -O2 -Xs %file_name%',
            'py' => IA_JUDGE_PY_COMPILER.' %file_name% %exe_name%',
    );
    $matches = array();
    if (!preg_match("/^(.*)\.(c|cpp|pas|fpc|py)$/i",
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
    $compiler_message = shell_exec("$cmdline 2>&1 | head -n 50");

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
function jail_run($program, $jaildir, $time, $memory, $capture_std = false) {
    eval_assert(is_whole_number($time));
    eval_assert(is_whole_number($memory));

    $cmdline = IA_ROOT_DIR . 'jrun/jrun';
    $cmdline .= " --prog=./" . $program;
    $cmdline .= " --dir=" . $jaildir;
    $cmdline .= " --chroot";
    //$cmdline .= " --verbose";
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
    if (isset($time)) {
        $cmdline .= " --time-limit=" . $time;
    }
    if (isset($memory)) {
        $cmdline .= " --memory-limit=" . $memory;
    }
    //$cmdline .= " --verbose";

    log_print("Running $cmdline");
    $message = shell_exec($cmdline);

    $result = jrun_parse_message($message);
    if ($result == false) {
        return jrun_make_error('Failed executing jail');
    }

    if ($capture_std) {
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

    return $result;
}
