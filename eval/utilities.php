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
// Can currently handle C, C++, FreePascal, Rust, Python and Java
function compile_file($input_file_name, &$compiler_type, &$compiler_message) {
    $compiler_message = false;
    $name_by_compiler = array(
        'c-32' => 'main.c',
        'cpp-32' => 'main.cpp',
        'c-64' => 'main.c',
        'cpp-64' => 'main.cpp',
        'fpc' => 'main.fpc',
        'pas' => 'main.pas',
        'rs' => 'main.rs',
        'py' => 'main.py',
        'java' => 'Main.java',
    );

    $compiler_lines = array(
            // Make sure -lm stays after source file & target output
            'c-32' => '/usr/bin/gcc -m32 -DINFOARENA -Wall -O2 -static '.
                      '-std=c11 %file_name% -o %exe_name% -lm',
            'cpp-32' => '/usr/bin/g++ -m32 -DINFOARENA -Wall -O2 -static '.
                        '-std=c++14 %file_name% -o %exe_name% -lm',
            'c-64' => '/usr/bin/gcc -m64 -DINFOARENA -Wall -O2 -static '.
                      '-std=c11 %file_name% -o %exe_name% -lm',
            'cpp-64' => '/usr/bin/g++ -m64 -DINFOARENA -Wall -O2 -static '.
                        '-std=c++14 %file_name% -o %exe_name% -lm',
            'pas' => '/usr/bin/fpc -O2 -Xs %file_name% -dINFOARENA',
            'fpc' => '/usr/bin/fpc -O2 -Xs %file_name% -dINFOARENA',
            'rs' => '/cargo/bin/rustc -O %file_name% -o %exe_name%',
            'py' => '/usr/bin/python3 -m py_compile %file_name%',
            'java' => '/usr/bin/javac %file_name%',
        );
    $compiler_mounts = array(
        'c-32' => array(
        '/lib:/lib:exec',
        '/lib32:/lib32:exec',
                        '/lib64:/lib64:exec',
                        '/usr/lib32:/usr/lib32:exec',
                        '/usr/bin:/usr/bin:exec',
                        '/usr/lib:/usr/lib:exec',
                        '/usr/include:/usr/include',
        ),
        'cpp-32' => array(
        '/lib:/lib:exec',
        '/lib32:/lib32:exec',
                          '/lib64:/lib64:exec',
                          '/usr/lib32:/usr/lib32:exec',
                          '/usr/bin:/usr/bin:exec',
                          '/usr/lib:/usr/lib:exec',
                          '/usr/include:/usr/include',
        ),
        'c-64' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                        '/usr/bin:/usr/bin:exec',
                        '/usr/lib:/usr/lib:exec',
                        '/usr/include:/usr/include',
        ),
        'cpp-64' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                          '/usr/bin:/usr/bin:exec',
                          '/usr/lib:/usr/lib:exec',
                          '/usr/include:/usr/include',
        ),
        'pas' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                       '/usr/bin/:/usr/bin:exec',
                       '/usr/lib:/usr/lib:exec',
                       '/etc/alternatives:/etc/alternatives:exec',
        ),
        'fpc' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                       '/usr/bin/:/usr/bin:exec',
                       '/usr/lib:/usr/lib:exec',
                       '/etc/alternatives:/etc/alternatives:exec',
        ),
        'rs' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                      '/usr/bin:/usr/bin:exec',
                      '/usr/lib:/usr/lib:exec',
                      '/etc/alternatives:/etc/alternatives:exec',
                      IA_JUDGE_CARGO_PATH.':/cargo:exec',
                      IA_JUDGE_RUSTUP_PATH.':/rustup:exec',
        ),
        'py' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                      '/usr/bin:/usr/bin:exec',
                      '/usr/lib:/usr/lib:exec',
        ),
        'java' => array(
        '/lib:/lib:exec',
        '/lib64:/lib64:exec',
                        '/usr/bin:/usr/bin:exec',
                        '/usr/lib:/usr/lib:exec',
                        '/etc/alternatives:/etc/alternatives:exec',
        ),
    );

    $matches = array();
    if (!preg_match(
        '/^(.*)\.(c|cpp|pas|fpc|rs|py|java|cpp-64|cpp-32|c-64|c-32|c++)$/i',
        $input_file_name, $matches)) {
        $compiler_message = "Nu am putut sa determin compilatorul ".
                "pentru '$input_file_name'.";
        return false;
    }
    $exe_name = 'main';
    $extension = $matches[2];

    if (is_null($compiler_type)) {
        $compiler_type = $extension;
    }

    // Old submissions were 32 bit, try to be consistent and not change that.
    if ($compiler_type == 'c' || $compiler_type == 'cpp') {
        $compiler_type .= '-' . IA_DEFAULT_EVAL_ARCH;
    }

    if (!isset($compiler_lines[$compiler_type])) {
        $compiler_message = "Nu stiu cum sa compilez fisiere '$compiler_type'";
        return false;
    }

    $source_name = $name_by_compiler[$compiler_type];
    eval_assert(@rename($input_file_name, $source_name),
                'Could not rename user file name to general name');
    $cmdline = $compiler_lines[$compiler_type];
    $cmdline = preg_replace('/%file_name%/', $source_name, $cmdline);
    $cmdline = preg_replace('/%exe_name%/', $exe_name, $cmdline);

    $mounts = $compiler_mounts[$compiler_type];
    $envs = array();

    if ($compiler_type == 'rs') {
        $envs['CARGO_HOME'] = '/cargo';
        $envs['RUSTUP_HOME'] = '/rustup';
        $envs['TMPDIR'] = '/';
    } else if ($compiler_type == 'py') {
        // python really wants a home directory
        $envs['HOME'] = '/';
    }

    // Running compiler

    $parts = explode(' ', $cmdline, 2);
    $result = jail_run($parts[0], getcwd().'/', IA_JUDGE_COMPILE_TIMELIMIT,
                       IA_JUDGE_COMPILE_MEMLIMIT, true, array(), $mounts,
                       'compile', false, $envs, $parts[1]);

    $compiler_message = $result['stdout'].$result['stderr'];

    // special failure for java
    if ($compiler_type == 'java' && !@file_exists('Main.class') &&
        $result['result'] == 'OK') {
        $result['result'] = 'FAIL';

        $compiler_message = "In fisierul trimis trebuie sa se gaseasca o clasa".
                            " publica numita Main.\n".$compiler_message;
    }

    $compiler_message = explode("\n", $compiler_message);
    $compiler_message = array_slice($compiler_message, 0, 50);
    $compiler_message = implode("\n", $compiler_message);


    if ($result['result'] == 'FAIL') {
        return false;
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
function jail_run($program, $jaildir, $time, $memory, $capture_std = true,
                  $redirect_std = array(), $mounts = array(),
                  $instance_name = 'default', $async = false,
                  $extra_env = array(), $extra_args = '') {
    eval_assert(is_whole_number($time) || is_array($time));
    eval_assert(is_whole_number($memory));
    eval_assert(is_array($redirect_std));
    eval_assert(!$capture_std || !$redirect_std,
                'Can not have $capture_std and $redirect_std at the same time');

    // FIXME: for some reasons on certain linux kernels the memory is not
    // cleaned up properly, so just remove them for a clean run. If this ever
    // gets fixed remove these.
    @system("rmdir /sys/fs/cgroup/memory/ia-sandbox/$instance_name/isolated");
    @system("rmdir /sys/fs/cgroup/memory/ia-sandbox/$instance_name");
    $cmdline = IA_SANDBOX_PATH;
    $cmdline .= ' --new-root '.$jaildir;

    if ($capture_std) {
        $cmdline .= ' --stdin /dev/null';
        $cmdline .= ' --stdout jailed_stdout';
        $cmdline .= ' --stderr jailed_stderr';
    }
    if (!$capture_std) {
        foreach (array('in', 'out', 'err') as $file) {
            if (array_key_exists($file, $redirect_std)) {
                $cmdline .= ' --std'.$file.' '.
                    escapeshellarg($redirect_std[$file]);
            } else {
                $cmdline .= ' --std'.$file.' /dev/null';
            }
        }
        if (getattr($redirect_std, 'out-before-in')) {
            $cmdline .= ' --swap-redirects';
        }
    }
    if ($mounts) {
        foreach ($mounts as $mount) {
            $cmdline .= ' --mount '.escapeshellarg($mount);
        }
    }
    if (isset($time)) {
        if (is_array($time)) {
            $cmdline .= ' --time '.$time['user'].'ms';
            $cmdline .= ' --wall-time '.$time['wall'].'ms';
        } else {
            $cmdline .= ' --time '.$time.'ms';
            $cmdline .= ' --wall-time '.($time + 2000).'ms';
        }
    }
    if (isset($memory)) {
        $cmdline .= ' --memory '.$memory.'kb';
        $cmdline .= ' --stack '.$memory.'kb';
    }

    $cmdline .= ' --env PATH=/usr/bin';
    if ($extra_env) {
        foreach ($extra_env as $key => $value) {
            $cmdline .= ' --env '.escapeshellarg($key.'='.$value);
        }
    }
    $cmdline .= ' --instance-name '.escapeshellarg($instance_name);

    $cmdline .= ' --output oneline';
    $cmdline .= ' '.$program;
    $cmdline .= ' -- '.$extra_args;

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

function run_file($compiler_id, $bin_path, $jail_dir, $time,
                  $memory, $capture_std = false, $redirect_std = array(),
                  $instance_name = 'default', $async = false) {
    $command = array(
        'c' => '/user_bin/main',
        'cpp' => '/user_bin/main',
        'c-32' => '/user_bin/main',
        'cpp-32' => '/user_bin/main',
        'c-64' => '/user_bin/main',
        'cpp-64' => '/user_bin/main',
        'pas' => '/user_bin/main',
        'fpc' => '/user_bin/main',
        'rs' => '/user_bin/main',
        'py' => '/usr/bin/python3',
        'java' => '/usr/bin/java',
    );

    $mounts = array($bin_path.':/user_bin:exec');
    $envs = array();
    $extra_args = '';

    eval_assert(array_key_exists($compiler_id, $command),
                "I don't know how to run user executable");
    if ($compiler_id == 'java') {
        $mounts = array_merge($mounts,
                              array(
                              '/lib:/lib:exec',
                              '/lib64:/lib64:exec',
                                    '/usr/bin:/usr/bin:exec',
                                    '/usr/lib:/usr/lib:exec',
                                    '/etc/alternatives:/etc/alternatives:exec',
                              ));

        $extra_args = ' -Xmx512m -Xss128m -DONLINE_JUDGE=true'.
                      ' -Duser.language=en -Duser.region=US'.
                      ' -Duser.variant=US -cp /user_bin Main';
    } else if ($compiler_id == 'rs') {
        $mounts = array_merge($mounts,
                              array(
                              '/lib:/lib:exec',
                              '/lib64:/lib64:exec',
                                    '/usr/lib:/usr/lib:exec',
                              ));
    } else if ($compiler_id == 'py') {
        $mounts = array_merge($mounts,
                              array(
                              '/lib:/lib:exec',
                              '/lib64:/lib64:exec',
                                    '/usr/bin:/usr/bin:exec',
                                    '/usr/lib:/usr/lib:exec',
                              ));
        $envs['HOME'] = '/';
        $extra_args = ' -m user_bin.main';
    }

    if ($compiler_id == 'py' || $compiler_id == 'java') {
        // a bit of help for startup
        if (is_array($time)) {
            $time['user'] += 30;
        } else {
            $time += 30;
        }
    }

    $program = $command[$compiler_id];
    return jail_run($program, $jail_dir, $time, $memory, $capture_std,
                    $redirect_std, $mounts, $instance_name, $async,
                    $envs, $extra_args);
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
            throw new EvalSystemError('Evaluator neconfigurat');
        }

        if ($jrun_process['capture_std']) {
            $jaildir = $jrun_process['jaildir'];
            $result['stdout'] = @file_get_contents($jaildir.'jailed_stdout');
            if ($result['stdout'] === false) {
                throw new EvalSystemError('Failed reading captured stdout');
            }
            $result['stderr'] = @file_get_contents($jaildir.'jailed_stderr');
            if ($result['stderr'] === false) {
                throw new EvalSystemError('Failed reading captured stderr');
            }
        }

        if ($result['result'] == 'OK') {
            if ($result['time'] > $jrun_process['time'] ||
                $result['memory'] > $jrun_process['memory']) {
                log_print(
                    'Jrun returns OK, but time or memory limit is broken');
            }
        }

        $results[] = $result;
    }
    return $results;
}
