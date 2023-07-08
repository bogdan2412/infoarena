<?php

if (!defined('IA_SETUP_SCRIPT')) {
    $script_dir = dirname($argv[0]);
    require_once($script_dir . "/../config.php");
    require_once(IA_ROOT_DIR . "common/log.php");
    require_once(IA_ROOT_DIR . "common/common.php");
    require_once(IA_ROOT_DIR . "common/db/db.php");

    if (realpath(IA_ROOT_DIR.'scripts') != realpath($script_dir)) {
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
    flush();
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
