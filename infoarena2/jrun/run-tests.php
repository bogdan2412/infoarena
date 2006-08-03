#! /usr/bin/env php
<?php

// Config options.
$jail_dir = "jail";
$exe_name = "prog";
$extra_args = "--uid 1005 --gid 1005 --copy-libs --nice -5 --block-syscalls-file=bad_syscalls";

// Exit with an error message.
function error($message)
{
    print($message);
    exit(0);
}

// Parse a source file
// Returns the arguments to run the jail with and the expected response.
// test_exp_res is a posix regex that should match the jail run response.
function parse_source($filename, &$test_args, &$test_exp_res)
{
    $test_args = $test_exp_res = null;
    foreach (file("$filename") as $line) {
        $pos = strpos($line, "JRUN_ARGS =");
        if ($pos !== FALSE) {
            $test_args = substr($line, $pos + strlen("JRUN_ARGS =") + 1);
        }
        $pos = strpos($line, "JRUN_RES =");
        if ($pos !== FALSE) {
            $test_exp_res = substr($line, $pos + strlen("JRUN_RES =") + 1);
        }
    }
    $test_args = trim($test_args);
    $test_exp_res = trim($test_exp_res);
    if ($test_args === null || $test_exp_res === null) {
        error("$filename doesn't mention JRUN_ARGS and JRUN_RES\n");
    }
}

function compile_source($source, $exe)
{
    if (strpos($source, ".cpp") === strlen($source) - 4) {
        system("g++ -Wall -lm -O2 $source -o $exe", $ret);
    } else if (strpos($source, ".c") === strlen($source) - 2) {
        system("gcc -Wall -lm -O2 $source -o $exe", $ret);
    } else {
        error("Can't compile $source, unknown file extension\n");
    }
    if ($ret) {
        error("Compilation error on $source\n");
    }
}

// Do a jail run.
// This will compile $filename in $jail_dir and jrun with the
// specified args. Returns jail output.
function jail_run($filename, $args)
{
    global $jail_dir, $exe_name;

    system("rm -rf jail");
    system("mkdir -p $jail_dir");
    system("chmod 777 $jail_dir");
    compile_source($filename, "$jail_dir/$exe_name");

    $cmd = "./jrun --dir=$jail_dir --prog=$exe_name $args";
    print("Executing $cmd\n");

    $res = shell_exec($cmd);
    system("rm -rf $jail_dir");
    return $res;
}

// Run one test.
// Returns true/false on success/failure.
function run_test($filename)
{
    global $extra_args;

    if (!file_exists($filename)) {
        error("$filename doesn't exists.\n");
    }

    parse_source($filename, &$test_args, &$test_exp_res);
    $test_res = jail_run($filename, "$test_args $extra_args");

    if (!ereg($test_exp_res, $test_res)) {
        $result = false;
        print("FAIL: $filename\n");
    } else {
        $result = true;
        print("OK: $filename\n");
    }
    print("wanted: $test_exp_res\n");
    print("got: $test_res\n");

    return $result;
}

// Main function.
function run_all()
{
    $tests_total = $tests_failed = 0;

    foreach (glob("tests/*") as $filename) {
        ++$tests_total;
        if (run_test($filename) == false) {
            ++$tests_failed;
        }
    }
    if ($tests_failed) {
        printf("Failed $tests_failed out of $tests_total (%.2lf%%)\n",
                100.0 * $tests_failed / $tests_total);
    } else {
        print("All $tests_total tests OK.\n");
    }
}

// Compile jail.
system("make", $ret);
if ($ret) {
    print("Jailer compilation failed, aborting\n");
    exit(-1);
}

if ($argc == 1) {
    run_all();
} else if ($argc == 2) {
    run_test($argv[1]);
} else {
    print("Invalid arguments\n");
}

?>
