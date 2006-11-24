<?php

// Nicer way to get an element from an array. It returns a default value
// (defaulting to null) instead of throwing an error.
function getattr($dict, $attribute, $default = null) {
    if (isset($dict[$attribute])) {
        return $dict[$attribute];
    } else {
        return $default;
    }
}

// Check if a a variable is a whole number.
function is_whole_number($x) {
    return is_numeric($x) && $x == intval($x);
}

// Validates page name
function is_page_name($page_name) {
    return preg_match('/^([a-z0-9][a-z0-9_\-\/\.]*)$/i', $page_name);
}

// returns boolean whether specified attach name is valid
// NOTE: We hereby limit file names. No spaces, please. Not that we have
// a problem with spaces inside URLs. Everything should be (and hopefully is)
// urlencode()-ed. However, practical experience shows it is hard to work with
// such file names, mostly due to URLs word-wrapping when inserted in texts,
// unless, of course, one knows how to properly escape spaces with %20 or +
function is_attachment_name($attach_name) {
    return preg_match('/^[a-z0-9][a-z0-9\.\-_]*$/i', $attach_name);
}

// Check if textblock security string is valid
// FIXME: check task/round existence?
function is_textblock_security_descriptor($descriptor)
{
    return  preg_match("/^ \s* task: \s* ([a-z0-9]*) \s* $/xi", $descriptor) ||
            preg_match("/^ \s* round: \s* ([a-z0-9]*) \s* $/xi", $descriptor) ||
            preg_match('/^ \s* (private|protected|public) \s* $/xi', $descriptor);
}

// Check if arg is a textblock.
function is_textblock($tb) {
    return is_array($tb) &&
           isset($tb['name']) && is_string($tb['name']) &&
           isset($tb['title']) && is_string($tb['title']) &&
           isset($tb['text']) && is_string($tb['text']) &&
           isset($tb['timestamp']) && is_datetime($tb['timestamp']) &&
           isset($tb['security']) && is_textblock_security_descriptor($tb['security']);
}

// tells whether $round_id is a valid round identifier
// Does not check existence.
function is_round_id($round_id) {
    return preg_match('/^[a-z0-9][a-z0-9_]*$/i', $round_id) && strlen($round_id) < 16;
}

// Checks if $round is a valid round.
function is_round($round) {
    return is_array($round) &&
           isset($round['title']) && is_string($round['title']) &&
           isset($round['page_name']) && is_page_name($round['page_name']) &&
           isset($round['user_id']) && is_whole_number($round['user_id']) &&
           isset($round['hidden']) && // How the fuck do I check this?
           isset($round['type']) && $round['type'] == 'classic' &&
           isset($round['id']) && is_round_id($round['id']);
}

// Tells whether $task_id is a valid task identifier
// Does not check existence.
function is_task_id($task_id) {
    return preg_match('/^[a-z0-9][a-z0-9_]*$/i', $task_id) && strlen($task_id) < 16;
}

// Checks if $task is a valid task.
function is_task($task) {
    return is_array($task) &&
           isset($task['title']) && is_string($task['title']) &&
           isset($task['author']) && is_string($task['author']) &&
           isset($task['source']) && is_string($task['source']) &&
           isset($task['page_name']) && is_page_name($task['page_name']) &&
           isset($task['user_id']) && is_whole_number($task['user_id']) &&
           isset($task['hidden']) && // How the fuck do I check this?
           isset($task['type']) && $task['type'] == 'classic' &&
           isset($task['id']) && is_task_id($task['id']);
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
    //log_warn("fileinfo extension failed, defaulting mime type to application/octet-stream.");
    return "application/octet-stream";
}

// Checks system requirements.
// This will fail early. Of something is missing.
function check_requirements()
{
    $extensions = get_loaded_extensions();

    if (version_compare(phpversion(), '5.0', '<')) {
        log_error("PHP 5.0 required.");
    }
    if (array_search('mysql', $extensions) === false) {
        log_error("mysql extension required.");
    }
    if (array_search('gd', $extensions) === false) {
        log_warn("gd extension required.");
    }
    if (array_search('zip', $extensions) === false) {
        log_error("zip extension required.");
    }
    if (!function_exists("finfo_open")) {
        log_warn("finfo_open missing, falling back to mime_content_type.");
        if (!function_exists("mime_content_type")) {
            log_warn("mime_content_type missing, mime-types will default to application/octet-stream.");
        }
    }
}

?>
