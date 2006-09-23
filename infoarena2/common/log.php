<?php

// This file contains a simple logging/debug api. You should always use these
// functions instead of print, print_r and echo.
//
// These functions wrap around php's error reporting, generating E_USER
// messages. Things like whether they are displayed in the output or merely
// logged is configurable in php.ini.
//
// NOTE: this is for development messages only. Users should never get to
// see them. There are other functions to deal with bad form values, or
// insufficient permissions or other usual errors.

// Print a simple message to the log. Use for informative messages.
function log_print($message)
{
    trigger_error($message, E_USER_NOTICE);
}

// Use this for warning messages.
function log_warn($message)
{
    trigger_error($message, E_USER_WARNING);
}

// Use this when you hit a serious problem, and can't recover.
// You might want to use log_die instead.
function log_error($message)
{
    trigger_error($message, E_USER_ERROR);
}

// Calls log_error and the die(). Does not return.
// Use this for things like failing a database connection.
function log_die($message)
{
    log_error($message);
    die();
}

// Check if $value is true, and if it isn't it prints and error and dies.
function log_assert($value, $message = "Assertion failed")
{
    if (!$value) {
        log_die($message);
    }
}

// Custom error_handler.
// This behaves just like the standard error handler(uses error_log and all),
// except it hacks file/line/function information for the log_xxx family of
// functions to display the callers file/line info.
//
// FIXME: print function name too? could be done.
function logging_error_handler($errno, $errstr, $errfile, $errline)
{
    // Maps error type constant names to messages headers.
    $errortypestr_message = array(
        'E_ERROR'              => 'PHP Error',
        'E_WARNING'            => 'PHP Warning',
        'E_NOTICE'             => 'PHP Notice',
        'E_PARSE'              => 'Parse Error',
        'E_CORE_ERROR'         => 'Core Error',
        'E_CORE_WARNING'       => 'Core Warning',
        'E_COMPILE_ERROR'      => 'Compile Error',
        'E_COMPILE_WARNING'    => 'Compile Warning',
        'E_STRICT'             => 'Runtime Notice',
        'E_USER_ERROR'         => 'User Error',
        'E_USER_WARNING'       => 'User Warning',
        'E_USER_NOTICE'        => 'User Notice',
        'E_RECOVERABLE_ERRROR' => 'Catchable Fatal Error',
    );

    // Maps error type constant to messages.
    // Built from errortypestr_message to cleanly handler missing constants in
    // older php versions.
    $errortype_message = array();

    // Build errortype_message.
    foreach ($errortypestr_message as $errortypestr => $message) {
        if (defined($errortypestr)) {
            $errortype_message[constant($errortypestr)] = $message;
        }
    }

    // Obey error_reporting from php_ini.
    // The @ operator works by changing error_reporting, so it will
    // work just fine.
    if ((error_reporting() & $errno) == 0) {
        return;
    }

    // Hack for log_print and all.
    if ($errno == E_USER_ERROR || $errno == E_USER_WARNING || $errno == E_USER_NOTICE) {
        $backtrace = debug_backtrace();
        // backtrace[0] is the current function, so we check for backtrace[1].
        if (($errno == E_USER_NOTICE  && $backtrace[1]['function'] = 'log_print') ||
            ($errno == E_USER_WARNING && $backtrace[1]['function'] = 'log_warn' ) ||
            ($errno == E_USER_ERROR   && $backtrace[1]['function'] = 'log_error')) {
            $errfile = $backtrace[2]['file'];
            $errline = $backtrace[2]['line'];
        }
    }

    // The behaviour of this function is defined with error_log in php.ini
    error_log("$errortype_message[$errno]: $errstr in $errfile line $errline");

    // Die on certain fatal errors:
    // 0x1055 = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR
    // FIXME: Why isn't this configurable in php.ini?
    // FIXME: What about moving to config.php?
    $fatal_errors = 0x1055;
    if ($fatal_errors & $errno) {
        die();
    }
}

// Change the default error handler.
set_error_handler("logging_error_handler");

// FIXME: log readability hack.
error_log("- -- --- ---- ----- New request marker ----- ---- --- -- -");

?>
