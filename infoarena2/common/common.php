<?php

// Nicer way to get an element from an array. It returns a default value
// (defaulting to null) instead of throwing an error.
function getattr($dict, $attribute, $defaultValue = null) {
    if (isset($dict[$attribute])) {
        return $dict[$attribute];
    }
    else {
        return $defaultValue;
    }
}

function is_whole_number($x) {
    return is_numeric($x) && $x == intval($x);
}

// tells whether given string is a valid datetime value
// see parse_datetime()
function is_datetime($string) {
    $timestamp = parse_datetime($string);
    return (false !== $timestamp);
}

// parse value of a datetime parameter
// i.e.: 2006-11-27 23:59:59
//
// returns unix timestamp or FALSE upon error
function parse_datetime($string) {
    $res = strptime($string, '%Y-%m-%d %T');

    if (!$res) {
        return false;
    }

    return mktime($res['tm_hour'], $res['tm_min'], $res['tm_sec'],
                  1, $res['tm_yday']+1, $res['tm_year']+1900);
}

// formats unix timestamp as a datetime parameter value
// i.e.: 2006-11-27 23:59:59
function format_datetime($timestamp) {
    return strftime('%Y-%m-%d %T', $timestamp);
}

// Get a file's mime type.
function get_mime_type($filename)
{
    if (function_exists("finfo_open")) {
        // FIXME: cache.
        $finfo = @finfo_open(FILEINFO_MIME, '/usr/share/misc/file/magic');
        if ($finfo !== false) {
            $res = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $res;
        }
    }
    if (function_exists("mime_content_type")) {
        $res = @mime_content_type($filename);
        if ($res !== false) {
            return $res;
        }
    }
    log_warn("fileinfo extension failed, defaulting mime type to application/octet-stream.");
    return "application/octet-stream";
}

// Checks system requirements.
// This will fail early. if something is missing.
function check_requirements()
{
    $phpver = phpversion();
    $finfover = phpversion("fileinfo");
    $mysqlver = phpversion("mysql");
    $gdver = phpversion("gd2");
    $zipver = phpversion("zip");

    $msg = "PHP v$phpver";
    if ($mysqlver) $msg .= " mysql v$mysqlver";
    if ($gdver) $msg .= " gd v$gdver";
    if ($finfover) $msg .= " finfo v$finfover";
    if ($zipver) $msg .= " zip v$zipver";

    log_print("Running on $msg");

    if (!$mysqlver) {
        log_error("mysql extension required.");
    }
    if (!$finfover) {
        log_warn("fileinfo extension missing, mime-types will default to application/octet-stream.");
    }
    if (!$gdver) {
        log_warn("gd extension required.");
    }
    if (!$zipver) {
        log_error("zip extension required.");
    }
}

?>
