<?php

// Find path.
$curdir = dirname($argv[0]);

require_once($curdir . "/../config.php");
require_once(IA_ROOT . "common/log.php");
require_once(IA_ROOT . "common/common.php");
require_once(IA_ROOT . "common/db/db.php");

if (realpath(IA_ROOT . 'scripts') != realpath($curdir)) {
    log_error("You should only include this file from scripts");
}

check_requirements();

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
        if (preg_match("/t|true|y|yes|d|da/i", $answer)) {
            return true;
        }
        if (preg_match("/f|false|n|no|nu/i", $answer)) {
            return false;
        }
        echo "Raspunde da/nu/true/false/etc. Ceva sa inteleg si eu.";
    }
}

// Magically execute a $cmd.
// $cmd takes over the entire process.
// This is a lot harder than it sounds.
function magic_exec($cmd)
{
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
        pcntl_exec($exe, array_slice($argv, 1));
    }
}

?>
