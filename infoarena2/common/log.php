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

// Print a simple message to the log. Use for informative messages.
function log_print($message, $include_origin = false) {
    if ($include_origin) {
        $message = format_message_backtrace($message);
    }
    trigger_error($message, E_USER_NOTICE);
}

// Use this for warning messages.
function log_warn($message, $include_origin = false) {
    if ($include_origin) {
        $message = format_message_backtrace($message);
    }
    trigger_error($message, E_USER_WARNING);
}

// Use this when you hit a serious problem, and can't recover.
// You might want to use log_error instead.
function log_error($message, $include_origin = false) {
    if ($include_origin) {
        $message = format_message_backtrace($message);
    }
    trigger_error($message, E_USER_ERROR);
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

// Check if $value is true, and if it isn't it prints and error and dies.
function log_assert($value, $message = "Assertion failed", $include_origin = true) {
    if (!$value) {
        if ($include_origin) {
            $message = format_message_backtrace($message);
        }
        log_error($message, false);
    }
}

// Private function used to implement log_assert_is_* functions.
function _log_assert_type($obj, $msg, $inc_origin, $checker, $typename) {
    if (!$checker($obj)) {
        if ($msg === false) {
            $msg = "$obj is not $typename";
        }
        if ($inc_origin) {
            $msg = format_message_backtrace($msg, 2);
        }
        log_error($msg, false);
    }
}

// Check if something is_callable.
function log_assert_is_callable($obj, $msg = false, $inc_origin = true) {
    _log_assert_type($obj, $msg, $inc_origin, 'is_callable', 'callable');
}

// Check if something is_array.
function log_assert_is_array($obj, $msg = false, $inc_origin = true) {
    _log_assert_type($obj, $msg, $inc_origin, 'is_array', 'an array');
}

// Check if an array has a certain key.
// Also checks if the obj is actually an array.
// Returns $obj[$key] if the assertion holds.
function log_assert_getattr($obj, $key, $msg = false, $inc_origin = true) {
    if (!is_array($obj)) {
        if ($msg === false) {
            $msg = "$obj is not an array";
        }
    } else if (!array_key_exists($key, $obj)) {
        if ($msg === false) {
            $msg = "$obj doesn't have '$key' key";
        }
    } else {
        return $obj[$key];
    }
    if ($inc_origin) {
        $msg = format_message_backtrace($msg, 1);
    }
    log_error($msg, false);
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
        die();
    }
}

// Change the default error handler.
set_error_handler("logging_error_handler");
?>
