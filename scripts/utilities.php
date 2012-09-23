<?php

if (!defined('IA_SETUP_SCRIPT')) {
    $script_dir = dirname($argv[0]);
    require_once($script_dir . "/../config.php");
    require_once(IA_ROOT_DIR . "common/log.php");
    require_once(IA_ROOT_DIR . "common/common.php");
    require_once(IA_ROOT_DIR . "common/db/db.php");

    if (realpath(IA_ROOT_DIR . 'scripts') != realpath($script_dir)) {
        log_error("You should only include this file from scripts");
    }

    check_requirements();
}

// Asks the user a question.
// $default is the default answer
function read_line($question, $default = null) {
    if ($default === null) {
        echo "$question ";
    } else {
        echo "$question"." (default:$default) ";
    }
    $r = trim(fgets(STDIN));
    if ($r == "") {
        $r = $default;
    }
    return $r;
}

// Same as read_line, but returns true/false.
// default must be true/false, or null.
function read_bool($question, $default = null) {
    while (true) {
        if ($default === null) {
            $answer = read_line($question);
        } else if ($default) {
            $answer = read_line($question, "yes");
        } else {
            $answer = read_line($question, "no");
        }
        if (preg_match("/^(true|y|yes|da)$/i", $answer)) {
            return true;
        }
        if (preg_match("/^(false|n|no|nu)$/i", $answer)) {
            return false;
        }
        echo "Answer with true/false/yes/no/etc.\n";
    }
}

// Magically execute a $cmd.
// $cmd takes over the entire process.
// This is a lot harder than it sounds.
function magic_exec($cmd) {
    $argv = preg_split("/\s/", $cmd, -1, PREG_SPLIT_NO_EMPTY);

    $prog = $argv[0];
    // Only search path if no slashes.
    if (strstr($prog, "/") === false) {
        foreach (explode(':', getenv("PATH")) as $dir) {
            $exe = realpath($dir . '/' . $prog);
            //log_print("Try $exe.");
            if ($exe !== false && is_executable($exe)) {
                break;
            }
            $exe = null;
        }
    } else {
        $exe = realpath($prog);
        if (!is_executable($exe)) {
            $exe = null;
        }
    }

    if ($exe === null) {
        log_error("Couldn't find '$prog' executable.");
    } else {
        pcntl_exec($exe, array_slice($argv, 1), $_ENV);
    }
}

function temp_dir() {
    if (function_exists('sys_get_temp_dir')) {
        return sys_get_temp_dir();
    } else {
        return '/tmp';
    }
}

function backup_timestamp() {
    return date("Ymd");
}

function remove_old_files($dir, $keep_newest = 10) {
    log_assert(is_dir($dir));
    $files = array();
    exec(sprintf("ls -t1 '%s'", $dir), $files);
    $delete_files = array_slice($files, $keep_newest);
    foreach ($delete_files as $file) {
        unlink($file);
    }
}

function is_backup_filename($filename, &$matches) {
    $pattern = "/^db-(\d{4})(\d{2})(\d{2})\.sql\.gz\.gpg$/";
    return preg_match($pattern, $filename, $matches);
}
