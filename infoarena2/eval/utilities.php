<?php

// Sleeps for a number of miliseconds.
function milisleep($ms) {
    usleep($ms * 1000);
}

// print with a timestamp
function tprint($msg) {
    print(date("d-m-y H:i:s") . ": $msg\n");
}

// Delete and remake a directory.
function clean_dir($dir)
{
    system("rm -rf " . $dir, $res);
    system("mkdir -m 0777 -p " . $dir, $res);
    if ($res) {
        tprint("Failed cleaning up directory $dir");
        return false;
    }
    tprint("Cleaned up directory $dir");
    return true;
}

// Compile a certain file.
// Returns success value.
function compile_file($file_name, &$compiler_message)
{
    $compiler_lines = array(
            'c' => 'gcc -Wall -O2 -static -lm %file_name% -o %exe_name%',
            'cpp' => 'g++ -Wall -O2 -static -lm %file_name% -o %exe_name%',
            'pas' => 'fpc -O2 -Xs %file_name%');
    if (!preg_match("/^(.*)\.(c|cpp|pas)$/i", $file_name, $matches)) {
        tprint("Can't figure out compiler for file $file_name");
        return false;
    }
    $exe_name = $matches[1];
    $extension = $matches[2];
    tprint("Compiling file '$file_name' extension '$extension'");
    if (!isset($compiler_lines[$extension])) {
        tprint("Can't find compiler line for extension $extension");
        return false;
    }

    $cmdline = $compiler_lines[$extension];
    $cmdline = preg_replace('/%file_name%/', $file_name, $cmdline);
    $cmdline = preg_replace('/%exe_name%/', $exe_name, $cmdline);

    tprint("Running $cmdline");
    @system("$cmdline &> compiler.log");
    if ($res) {
        tprint("Compilation failed");
        return false;
    }
    $compiler_message = file_get_contents('compiler.log');
    if ($compiler_message === false) {
        tprint("Failed getting compiler messages");
        $compiler_message = false;
        return false;
    }
    tprint($compiler_messages);
    return true;
}

function jail_run($program, &$time_limit, &$memory_limit)
{
    $cmdline = IA_JRUN_PATH;
    $cmdline .= " --prog=./" . $program;
    $cmdline .= " --dir=" . IA_EVAL_JAIL_DIR;
    if (defined(IA_EVAL_JAIL_UID)) {
        $cmdline .= " --uid=" . IA_EVAL_JAIL_UID;
    }
    if (defined(IA_EVAL_JAIL_GID)) {
        $cmdline .= " --gid=" . IA_EVAL_JAIL_GID;
    }
    if (isset($time_limit)) {
        $cmdline .= " --time-limit=" . $time_limit;
    }
    if (isset($memory_limit)) {
        $cmdline .= " --memory-limit=" . $memory_limit;
    }

    ob_start();
    @system($cmdline);
    $message = ob_get_contents();
    ob_end_clean();

    tprint("Executed $cmdline");
    tprint("Got back $message");

    return $message;
}

?>
