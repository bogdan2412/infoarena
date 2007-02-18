<?php

// Sleeps for a number of miliseconds.
function milisleep($ms) {
    usleep($ms * 1000);
}

// Delete and remake a directory.
function clean_dir($dir)
{
    system("rm -rf " . $dir, $res);
    system("mkdir -m 0777 -p " . $dir, $res);
    if ($res) {
        log_print("Failed cleaning up directory $dir");
        return false;
    }
    log_print("Cleaned up directory $dir");
    return true;
}

// Compile a certain file.
// Returns success value.
function compile_file($file_name, &$compiler_message)
{
    $compiler_message = false;
    $compiler_lines = array(
            // Make sure -lm stays after source file & target output
            'c' => 'gcc -Wall -O2 -static %file_name% -o %exe_name% -lm',
            'cpp' => 'g++ -Wall -O2 -static %file_name% -o %exe_name% -lm',
            'pas' => 'fpc -O2 -Xs %file_name%',
            'fpc' => 'fpc -O2 -Xs %file_name%',
    );
    if (!preg_match("/^(.*)\.(c|cpp|pas|fpc)$/i", $file_name, $matches)) {
        log_print("Can't figure out compiler for file $file_name");
        return false;
    }
    $exe_name = $matches[1];
    $extension = $matches[2];
    log_print("Compiling file '$file_name' extension '$extension'");
    if (!isset($compiler_lines[$extension])) {
        log_warn("Can't find compiler line for extension $extension");
        return false;
    }

    $cmdline = $compiler_lines[$extension];
    $cmdline = preg_replace('/%file_name%/', $file_name, $cmdline);
    $cmdline = preg_replace('/%exe_name%/', $exe_name, $cmdline);

    // Running compiler
    //log_print("Running $cmdline");
    @system("$cmdline 2>&1 | head -n 25 &> compiler.log");

    // This is the BEST way to determine if compilation worked.
    $res = is_executable($exe_name);

    // Get compiler messages.
    $compiler_message = file_get_contents('compiler.log');
    if ($compiler_message === false) {
        log_print("Failed getting compiler messages");
        $compiler_message = false;
        return false;
    }
    //log_print("Compiler " . ($res ? 'succeeded' : 'failed') . " and said:\n$compiler_message\n");

    return $res;
}

// Parses jrun output.
// Returns an array with result, time, memory and message.
//
// Result is 'OK', 'FAIL' or 'ERROR'
// If result is ERROR time and memory are not available
// Returns false on error.
function jrun_parse_message($message)
{
    if (!preg_match("/^(ERROR|FAIL|OK):\ (.*)$/", $message, $matches)) {
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
function jrun_make_error($message)
{
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
function jail_run($program, $jaildir, $time, $memory, $capture_std = false)
{
    log_assert(is_whole_number($time));
    log_assert(is_whole_number($memory));
    $cmdline = IA_JRUN_EXE;
    $cmdline .= " --prog=./" . $program;
    $cmdline .= " --dir=" . $jaildir;
    $cmdline .= " --chroot";
    $cmdline .= " --block-syscalls-file=" . IA_JRUN_DIR . 'bad_syscalls';
    if (defined('IA_EVAL_JAIL_NICE') && IA_EVAL_JAIL_NICE != 0) {
        $cmdline .= " --nice=" . IA_EVAL_JAIL_NICE;
    }
    if (defined('IA_EVAL_JAIL_UID')) {
        $cmdline .= " --uid=" . IA_EVAL_JAIL_UID;
    }
    if (defined('IA_EVAL_JAIL_GID')) {
        $cmdline .= " --gid=" . IA_EVAL_JAIL_GID;
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
        $result['stdout'] = file_get_contents($jaildir.'jailed_stdout');
        if ($result['stdout'] === false) {
            return jrun_make_error('Failed reading captured stdout');
        }
        $result['stderr'] = file_get_contents($jaildir.'jailed_stderr');
        if ($result['stderr'] === false) {
            return jrun_make_error('Failed reading captured stderr');
        }
    }

    if ($result['message'] == 'ok') {
        log_assert($result['time'] < $time, "JRun says ok, but reports time > timelimit");
        log_assert($result['memory'] < $memory, "JRun says ok, but reports memory > memlimit");
    }

    return $result;
}

?>
