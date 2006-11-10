<?php

// This file contains a simple logging/debug api. You should always use these
// functions instead of print, print_r and echo.
//
// These functions wrap around php's error reporting, generating E_USER
// messages. Things like whether they are displayed in the output or merely
// logged is configurable in php.ini.
//
// The optional $include_origin parameter determines if the origin of the
// message (file and line right now) is appended to the message. It defaults
// to false except for assertions.
//
// NOTE: this is for development messages only. Users should never get to
// see them. There are other functions to deal with bad form values, or
// insufficient permissions or other usual errors.

// Private function.
// Formats a backtrace level as a string.
// $backtrace is the backtrace as received from a call to debug_backtrace.
// If ommited debug_backtrace will be called
//
// false is returned on error (like level out of range).
function format_backtrace($level, $backtrace = false) {
    // Generate backtrace if missing.
    if ($backtrace === false) {
        $backtrace = debug_backtrace();
    }

    // Level out of range.
    if (!isset($backtrace[$level])) {
        return false;
    }

    // Filename. Strips IA_ROOT.
    $file = false;
    if (isset($backtrace[$level]['file'])) {
        $file = $backtrace[$level]['file'];
        $file = preg_replace("/^".preg_quote(IA_ROOT, '/')."/", "", $file);
    }

    // Source line.
    $line = false;
    if (isset($backtrace[$level]['line'])) {
        $line = $backtrace[$level]['line'];
    }

    // Function name. Includes class name for methods.
    $func = false;
    if (isset($backtrace[$level + 1]) && isset($backtrace[$level + 1]['function'])) {
        $btl = $backtrace[$level + 1];
        if (isset($btl['class']) && isset($btl['type'])) {
            $func = $btl['class'].$btl['type'].$btl['function'];
        } else {
            $func = $btl['function'];
        }
    }
    // Don't print these functions.
    if ($func == 'require_once' || $func == 'require' ||
        $func == 'include_once' || $func == 'include') {
        $func = false;
    }

    $msg = "";
    if ($func !== false) {
        $msg .= "function $func ";
    }
    if ($file !== false) {
        $msg .= "file $file ";
        if ($line !== false) {
            $msg .= "line $line ";
        }
    }
    return substr($msg, 0, strlen($msg) - 1);
}

// Private function.
// Add backtrace info to a message.
function format_message_backtrace($message, $backtrace_level = 0) {
    return $message . " in " . format_backtrace($backtrace_level + 2);
}

// trigger_error which split multi-line strings.
function trigger_error_split($error_msg, $error_type)
{
    $error_msg = (string)$error_msg;
    foreach (explode("\n", $error_msg) as $error_line) {
        trigger_error($error_line, $error_type);
    }
}

// Print a simple message to the log. Use for informative messages.
function log_print($message, $include_origin = false) {
    if ($include_origin) {
        $message = format_message_backtrace($message);
    }
    trigger_error_split($message, E_USER_NOTICE);
}

// Use this for warning messages.
function log_warn($message, $include_origin = false) {
    if ($include_origin) {
        $message = format_message_backtrace($message);
    }
    trigger_error_split($message, E_USER_WARNING);
}

// Use this when you hit a serious problem, and can't recover.
// You might want to use log_error instead.
function log_error($message, $include_origin = false) {
    if ($include_origin) {
        $message = format_message_backtrace($message);
    }
    trigger_error_split($message, E_USER_ERROR);
}

// Print a complete backtrace, using log_print.
function log_backtrace($start_level = 0, $backtrace = false, $straight_to_log = false)
{
    // Generate backtrace if missing.
    if ($backtrace === false) {
        $backtrace = debug_backtrace();
    }

    // Do the dew.
    for ($i = $start_level; $i < count($backtrace); ++$i) {
        $msg = " - Backtrace Level $i: ".format_backtrace($i, $backtrace);
        if ($straight_to_log) {
            error_log($msg);
        } else {
            log_print($msg, false);
        }
    }
}

// var_dump to the log.
function log_var_dump($var)
{
    ob_start();
    var_dump($var);
    $msg = ob_get_clean();
    log_print($msg);
}

// print_r to the log.
function log_print_r($var)
{
    ob_start();
    print_r($var);
    $msg = ob_get_clean();
    log_print($msg);
}

// Check if $value is true, and if it isn't it prints and error and dies.
function log_assert($value, $message = "Assertion failed", $include_origin = true) {
    if (!$value) {
        if ($include_origin) {
            $message = format_message_backtrace($message);
        }
        log_error($message, false);
    }
}

// Custom error_handler.
// This behaves as close standard error handler as possible, it uses
// error_log and all. file/line information is not printed for messages
// originating in the log_* family of functions.
//
// FIXME: print function name too? could be done.
function logging_error_handler($errno, $errstr, $errfile, $errline) {
    // Obey error_reporting from php_ini.
    // The @ operator works by changing error_reporting, so it will
    // work just fine.
    if ((error_reporting() & $errno) == 0) {
        return;
    }

    // Maps error type constant names to message prefixes.
    $errortypestr_msgprefix = array(
        'E_ERROR'              => 'PHP Error: ',
        'E_WARNING'            => 'PHP Warning: ',
        'E_NOTICE'             => 'PHP Notice: ',
        'E_PARSE'              => 'Parse Error: ',
        'E_CORE_ERROR'         => 'Core Error: ',
        'E_CORE_WARNING'       => 'Core Warning: ',
        'E_COMPILE_ERROR'      => 'Compile Error: ',
        'E_COMPILE_WARNING'    => 'Compile Warning: ',
        'E_STRICT'             => 'Runtime Notice: ',
        'E_USER_ERROR'         => 'Error: ',
        'E_USER_WARNING'       => 'Warning: ',
        'E_USER_NOTICE'        => '',
        'E_RECOVERABLE_ERRROR' => 'Catchable Fatal Error: ',
    );

    // Maps error type constant to messages.
    // Built from errortypestr_message to cleanly handler missing constants in
    // older php versions.
    $errortype_msgprefix = array();

    // Build errortype_msgprefix.
    // FIXME: cache?
    foreach ($errortypestr_msgprefix as $errortypestr => $message) {
        if (defined($errortypestr)) {
            $errortype_msgprefix[constant($errortypestr)] = $message;
        }
    }

    // Hack for log_print and all.
    $include_backtrace = true;
    if ($errno == E_USER_ERROR || $errno == E_USER_WARNING || $errno == E_USER_NOTICE) {
        $backtrace = debug_backtrace();
        // backtrace[0] is the current function, so we check for backtrace[1].
        if (($errno == E_USER_NOTICE  && $backtrace[1]['function'] = 'log_print') ||
            ($errno == E_USER_WARNING && $backtrace[1]['function'] = 'log_warn' ) ||
            ($errno == E_USER_ERROR   && $backtrace[1]['function'] = 'log_error')) {
            $include_backtrace = false;
        }
    }

    if ($include_backtrace) {
        //$errstr = format_message_backtrace($errstr, 1);
        $errstr = "$errstr in $errfile line $errline";
    }

    // Include message prefix.
    $errstr = $errortype_msgprefix[$errno] . $errstr;

    // HACK: Add timestamp if desired.
    if (defined('LOG_TIMESTAMP_FORMAT')) {
        $errstr = date(LOG_TIMESTAMP_FORMAT) . ": " . $errstr;
    }

    // The behaviour of this function is defined with error_log in php.ini
    if (LOG_FATAL_ERRORS & $errno) {
        $errstr = "Fatal: " . $errstr;
    }
    error_log($errstr);

    // Die on certain fatal errors:
    if (LOG_FATAL_ERRORS & $errno) {
        // Print a full backtrace on fatal errors.
        error_log("Caught a fatal error, printing a full backtrace");
        log_backtrace(2, false, true);

        if (IA_DEBUG_MODE && defined('IA_INSIDE_WWW')) {
            //echo '<html><head><title>Info-arena2 made a booboo</title></head><body>';
            echo $errstr.' <br /> Printing full backtrace:';
            echo '<ul>';
            $backtrace = debug_backtrace();
            for ($i = 1; $i < count($backtrace); ++$i) {
                echo "<li>Backtrace Level $i: ".format_backtrace($i, $backtrace) . "</li>";
            }
            echo '</ul>';
            //echo '</body></html>';
        }
        die();
    }
}

// Change the default error handler.
set_error_handler("logging_error_handler");
?>
