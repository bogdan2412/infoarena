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
// Formats a backtrace entry as a string.
// FIXME: include function/method?
function format_backtrace($backtrace)
{
    $file = $backtrace['file'];
    // Strip IA_ROOT.

    $file = preg_replace("/^".preg_quote(IA_ROOT, '/')."/", "", $file);
    $line = $backtrace['line'];
    return "file $file line $line";
}

// Private function.
// Add backtrace info to a message.
function format_message_backtrace($message, $backtrace_level = 0)
{
    $bt = debug_backtrace();
    $btl = $backtrace_level;
    if ($btl < count($bt)) {
        return $message . " in " . format_backtrace($bt[$btl]);
    }  else {
        return $message;
    }
}

// Print a simple message to the log. Use for informative messages.
function log_print($message, $include_origin = false)
{
    if ($include_origin) {
        $message = format_message_backtrace($message, 1);
    }
    trigger_error($message, E_USER_NOTICE);
}

// Use this for warning messages.
function log_warn($message, $include_origin = false)
{
    if ($include_origin) {
        $message = format_message_backtrace($message, 1);
    }
    trigger_error($message, E_USER_WARNING);
}

// Use this when you hit a serious problem, and can't recover.
// You might want to use log_die instead.
function log_error($message, $include_origin = false)
{
    if ($include_origin) {
        $message = format_message_backtrace($message, 1);
    }
    trigger_error($message, E_USER_ERROR);
}

// Calls log_error and the die(). Does not return.
// Use this for things like failing a database connection.
function log_die($message, $include_origin = true)
{
    log_error($message, $include_origin);
    die();
}

// Check if $value is true, and if it isn't it prints and error and dies.
function log_assert($value, $message = "Assertion failed", $include_origin = true)
{
    if (!$value) {
        if ($include_origin) {
            $message = format_message_backtrace($message, 1);
        }
        log_die($message, false);
    }
}

// Custom error_handler.
// This behaves as close standard error handler as possible, it uses
// error_log and all. file/line information is not printed for messages
// originating in the log_* family of functions.
//
// FIXME: print function name too? could be done.
function logging_error_handler($errno, $errstr, $errfile, $errline)
{
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
        $errstr = format_message_backtrace($errstr, 1);
    }

    // Include message prefix.
    $errstr = $errortype_msgprefix[$errno] . $errstr;

    // HACK: Add timestamp if desired.
    if (defined('LOG_TIMESTAMP_FORMAT')) {
        $errstr = date(LOG_TIMESTAMP_FORMAT) . ": " . $errstr;
    }

    // The behaviour of this function is defined with error_log in php.ini
    error_log($errstr);

    // Die on certain fatal errors:
    if (LOG_FATAL_ERRORS & $errno) {
        die();
    }
}

// Change the default error handler.
set_error_handler("logging_error_handler");
?>
