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

// This is a bunch of common regular expressions.
// NOTE: centralize all regular expressions here, don't copy paste
// This way we can avoid issues with one regexp allowing a dot in username
// and another not allowing it.
//
// Do not include // stuff, we should be able to nest them.
// Do not capture. use (?: lalala ) instead of ( lalala )
// Case insensitive unless specially mentioned. Assume /xi
// Prefix with IA_RE

// Valid page names. This allows //SanNdBox/ stuff
define("IA_RE_PAGE_NAME", '[a-z0-9][a-z0-9_\-\.\@\/]*');

// Valid normal page names. This only allows sand/box stuff
// Lowercase, words are separated by only one /(no trailing).
// CASE sensitive!!
define("IA_RE_NORMAL_PAGE_NAME", '
        (?: [a-z0-9] [a-z0-9_\-\.\@]* )
        (?: \/ [a-z0-9] [a-z0-9_\-\.\@]* )*');

// Short identifiers. FIXME: limit length here too?
define("IA_RE_ROUND_ID", '[a-z0-9][a-z0-9_\-\.]*');
define("IA_RE_TASK_ID", '[a-z0-9][a-z0-9_\-\.]*');
define("IA_RE_SCORE_NAME", '[a-z0-9][a-z0-9_\-\.]*');

define("IA_RE_USER_NAME", '[a-z0-9][a-z0-9_\-\.\@]*');

// Valid email. A complete check is not possible, see
// http://www.regular-expressions.info/email.html
define("IA_RE_EMAIL", '[^@]+@.+\..+');

// User full name. Your name can't be %$!
define("IA_RE_USER_FULL_NAME", '[a-z][a-z0-9\-\.\ ]+');

// Attachment names.
// Starts with letter or number, can also contain .-_
// No funky stuff.
define("IA_RE_ATTACHMENT_NAME", '[a-z0-9][a-z0-9\.\-_]*');

// External urls. Used by textile.
define("IA_RE_EXTERNAL_URL", '[a-z]+:\/\/|mailto:[^@]+@[^@]+|[^@]+@[^@]');

// Check if a a variable is a whole number.
function is_whole_number($x) {
    return is_numeric($x) && $x == intval($x);
}

// tell if email address seems to be valid
function is_valid_email($email) {
    return preg_match('/^'.IA_RE_EMAIL.'$/xi', $email);
}

// Normalize a page name. Removes extra slashes and lowercases.
// This should always be done before entering the database.
function normalize_page_name($page_name) {
    $path = preg_split('/\//', $page_name, -1, PREG_SPLIT_NO_EMPTY);
    return strtolower(implode('/', $path));
}

// Checks if textblock name is normalized.
// no double slashes, no slashes at the end, no capitalizations.
function is_normal_page_name($page_name) {
    return preg_match('/^'.IA_RE_NORMAL_PAGE_NAME.'$/xi', $page_name);
}

// Validates page name
function is_page_name($page_name) {
    return preg_match('/^'.IA_RE_PAGE_NAME.'$/xi', $page_name);
}

// returns boolean whether specified attach name is valid
// NOTE: We hereby limit file names. No spaces, please. Not that we have
// a problem with spaces inside URLs. Everything should be (and hopefully is)
// urlencode()-ed. However, practical experience shows it is hard to work with
// such file names, mostly due to URLs word-wrapping when inserted in texts,
// unless, of course, one knows how to properly escape spaces with %20 or +
function is_attachment_name($attach_name) {
    return preg_match('/^'.IA_RE_ATTACHMENT_NAME.'$/xi', $attach_name);
}

// FIXME: crappy check
function is_user_id($user_id) {
    return is_whole_number($user_id);
}

// FIXME: crappy check
function is_attachment_id($id) {
    return is_whole_number($id);
}

// tells whether $round_id is a valid round identifier
// Does not check existence.
function is_round_id($round_id) {
    return preg_match('/^'.IA_RE_ROUND_ID.'$/xi', $round_id) &&
        strlen($round_id) < 16;
}

// Check valid score names.
// Does not check existence. 
function is_score_name($score_name) {
    return preg_match('/^'.IA_RE_SCORE_NAME.'$/xi', $score_name) &&
        strlen($score_name) < 32;
}

// Tells whether $task_id is a valid task identifier
// Does not check existence.
function is_task_id($task_id) {
    return preg_match('/^'.IA_RE_TASK_ID.'$/xi', $task_id) &&
           strlen($task_id) < 16;
}

// Check user name
function is_user_name($user_name) {
    return preg_match('/^'.IA_RE_USER_NAME.'$/xi', $user_name);
}

// Check user full name (John Smith sr.)
function is_user_full_name($user_full_name) {
    return preg_match('/^'.IA_RE_USER_FULL_NAME.'$/xi', $user_full_name);
}

// Checks system requirements.
// This will fail early if something is missing.
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
        log_warn("gd extension missing.");
    }
    if (array_search('zip', $extensions) === false) {
        log_warn("zip extension missing.");
    }
    if (!function_exists("finfo_open")) {
        log_warn("finfo_open missing, falling back to mime_content_type.");
        if (!function_exists("mime_content_type")) {
            log_warn("mime_content_type missing, mime-types will default to application/octet-stream.");
        }
    }

    // Check for retarded php.ini settings.
    if (IA_HTTP_ENV) {
        log_assert(!ini_get("session.auto_start"),
                   "Please disable session.auto_start. It kills babies!");
        log_assert(ini_get("session.use_cookies"),
                   "Please enable session.use_cookies.");
        log_assert(ini_get("session.use_only_cookies"),
                   "Please enable session.use_only_cookies.");
        log_assert(!ini_get("register_globals"),
                   "Please disable register_globals. It makes baby Jesus cry!");
        log_assert(!ini_get("magic_quotes_gpc"),
                   "Please disable magic_quotes_gpc. Magic is for wussies!");
        log_assert(!ini_get("magic_quotes_runtime"),
                   "Please disable magic_quotes_runtime.");
    }
}

// Various initialization
// FIXME: it's WRONG for an include or require to execute code.
// FIXME: I have no idea on where to move these things.

// Force max error reporting.
error_reporting(0xFFFF);

// Initialize execution stats.
if (IA_DEVELOPMENT_MODE) {
    $execution_stats = array(
        'timestamp' => microtime(true),
        'queries' => 0,
        'log_copy' => '',
    );
}

// All timing our logic is done in UTC, the sensible way.
// FORCE default timezone.
if (function_exists("date_default_timezone_set")) {
    date_default_timezone_set("UTC");
}

?>
