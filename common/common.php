<?php

// Changes all special characters ș, ț, Ș, Ț to ş, ţ, Ş, Ţ
// commabelow to cedille and returns the modified text
// FIXME: reverse characters in to_change when windows XP dies
function text_change_special_chars($text) {
    if (!extension_loaded('mbstring')) {
        return $text;
    }

    $to_change = array("ș"=>"ş", "ț"=>"ţ", "Ș"=>"Ş", "Ț"=>"Ţ");

    foreach ($to_change as $bad=>$good) {
        $text = mb_ereg_replace($bad, $good, $text);
    }
    return $text;
}

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
        (?: [a-z0-9_\-\.\@\+\*\[\]]* )
        (?: \/ [a-z0-9_\-\.\@]* )*');

// Short identifiers. FIXME: limit length here too?
define("IA_RE_ROUND_ID", '[a-z0-9][a-z0-9_\-\.]*');
define("IA_RE_TASK_ID", '(?-i:[a-z0-9][a-z0-9_\-\.]*)');
define("IA_RE_TAG_NAME", '[a-z0-9\-\.\ \@\(\)]+');
define("IA_RE_SCORE_NAME", '[a-z0-9][a-z0-9_\-\.]*');
define("IA_RE_USER_NAME", '[_@a-z0-9][a-z0-9_\-\.\@]*');

// IPv4 address, doesn't check for values greater than 255.
define("IA_RE_IPV4", '(?:\d{1,3}\.){3}\d{1,3}');
// Full IPv6 address, doesn't match compressed IPv6 formats.
define("IA_RE_IPV6_NO_COMPRESS", '(?:[0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4}');
// IP address, matches IPv4 and non-compressed IPv6.
define("IA_RE_IP_ADDRESS", IA_RE_IPV4."|".IA_RE_IPV6_NO_COMPRESS);

// Valid email. A complete check is not possible, see
// http://www.regular-expressions.info/email.html
define("IA_RE_EMAIL", '[^@]+@.+\..+');

// User full name. Your name can't be %$!
define("IA_RE_USER_FULL_NAME", '[a-z0-9\-\.\ \@]+');

// Attachment names.
// Starts with letter or number, can also contain .-_
// No funky stuff.
define("IA_RE_ATTACHMENT_NAME", '[a-z0-9][a-z0-9\.\-_]*');

// External urls. Used by textile.
define("IA_RE_EXTERNAL_URL", '[a-z]+:\/\/|mailto:[^@]+@[^@]+|[^@]+@[^@]');

// Constants used by the task list filter
define("IA_TLF_ALL", '');
define("IA_TLF_UNSOLVED", '1');
define("IA_TLF_TRIED", '2');
define("IA_TLF_SOLVED", '3');

// Constants for IA user-defined rounds
// Users can't create rounds lasting 123141231223 hours
define("IA_USER_DEFINED_ROUND_DURATION_LIMIT", '48');
// Users can't create rounds 5412312421 days before the round starts
define("IA_USER_DEFINED_ROUND_DAYSBEFORE_LIMIT", '30');
// Users can't create user defined rounds with too many problems
define("IA_USER_DEFINED_ROUND_TASK_LIMIT", '25');

// Windows hack for checkdnsrr function.
// FIXME: Not fully tested!
if (!function_exists('checkdnsrr')) {
    log_warn("Function checkdnsrr does not exist. ".
             "Presuming Windows NT environment and reimplementing it.");

    function checkdnsrr($host_name, $rec_type = "MX") {
        if (empty($host_name)) {
            return false;
        }

        $result = array();
        exec("nslookup -type=$rec_type $host_name", $result);
        // Check each line to find the one that starts with the host name.
        // If it exists then the function succeeded.
        foreach ($result as $line) {
            if (eregi("^$host_name", $line)) {
                return true;
            }
        }

        // Otherwise there was no mail handler for the domain
        return false;
    }
}

// Check if a a variable is a whole number.
function is_whole_number($x) {
    return is_numeric($x) && $x == intval($x);
}

// tell if email address seems to be valid
function is_valid_email($email) {
    if (!preg_match('/^'.IA_RE_EMAIL.'$/xi', $email)) {
        return false;
    }
    $domain = explode("@", $email);
    $domain = array_pop($domain).'.';
    if (!checkdnsrr($domain, "MX")) {
        return false;
    }
    return true;
}

// Check for (apparently) valid IP address.
// It will match IPv4 and only non-compressed IPv6. Doesn't check
// for values greater than 255.
function is_valid_ip_address($ip_address) {
    return preg_match('/^'.IA_RE_IP_ADDRESS.'$/xi', $ip_address);
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

/**
 * Validates user page name
 *
 * @param  string  $page_name
 * @return array                returns an array containing the matched user
 */
function get_page_user_name($page_name) {
    $matches = array();
    preg_match("/^ ".
                preg_quote(IA_USER_TEXTBLOCK_PREFIX, '/').
                '('.IA_RE_USER_NAME.") (\/?.*) $/xi",
                $page_name, $matches);
    return $matches;
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
        strlen($round_id) < 64;
}

// Check valid score names.
// Does not check existence.
function is_score_name($score_name) {
    return preg_match('/^'.IA_RE_SCORE_NAME.'$/xi', $score_name) &&
        strlen($score_name) < 64;
}

// Tells whether $task_id is a valid task identifier
// Does not check existence.
function is_task_id($task_id) {
    return preg_match('/^'.IA_RE_TASK_ID.'$/xi', $task_id) &&
           strlen($task_id) < 64;
}

function is_blog_post($blog_post) {
    return is_page_name($blog_post) && substr($blog_post, 0, 5) == 'blog/';
}

// Check job id
function is_job_id($job_id) {
    return is_whole_number($job_id);
}

// Check user name
function is_user_name($user_name) {
    return preg_match('/^'.IA_RE_USER_NAME.'$/xi', $user_name) &&
           strlen($user_name) < 64;
}

// Check user full name (John Smith sr.)
function is_user_full_name($user_full_name) {
    return preg_match('/^'.IA_RE_USER_FULL_NAME.'$/xi', $user_full_name);
}

// Check tag id
function is_tag_id($tag_id) {
    return is_whole_number($tag_id);
}

// Check tag name
function is_tag_name($tag_name) {
    return preg_match('/^'.IA_RE_TAG_NAME.'$/xi', $tag_name) &&
           strlen($tag_name) < 64;
}

// Check tag type
function is_tag_type($tag_type) {
    return in_array($tag_type, array(
        'author', 'contest', 'year', 'round', 'age_group',
        'method', 'algorithm', 'tag'
    ));
}

// Check tag
function is_tag($tag) {
    if (!is_array($tag)) {
        return false;
    }
    if (!array_key_exists("name", $tag)) {
        return false;
    }
    if (!array_key_exists("type", $tag)) {
        return false;
    }
    if (!array_key_exists("parent", $tag)) {
        return false;
    }
    return (
        is_tag_name($tag["name"]) &&
        is_tag_type($tag["type"]) &&
        is_tag_id($tag["parent"])
    );
}

// Taggable objects
function is_taggable($obj) {
    return $obj == 'user' || $obj == 'task' || $obj == 'round' ||
           $obj == 'textblock';
}

/**
 * Returns whether or not the size type given is an existent one on the site
 *
 * @param  string  $size_type
 * @return bool
 */
function is_valid_size_type($size_type) {
    $size_types = array("full", "tiny", "small", "normal", "forum", "big");
    return in_array($size_type, $size_types);
}

// Cached version of create_function_cached.
// Never use create_function_cached directly because it's a memory leak.
// It's better to try avoid create_function_cached anyway, but if you
// have to use it it's better to use this cached version.
function create_function_cached($args, $code) {
    if (!IA_ENABLE_CREATE_FUNCTION_CACHE) {
        return create_function($args, $code);
    }
    static $_cache = array();
    $key = str_replace('|', '<|>', $args) . '|' .
           str_replace('|', '<|>', $code);
    if (!array_key_exists($key, $_cache)) {
        $_cache[$key] = create_function($args, $code);
    }
    return $_cache[$key];
}

// Checks system requirements.
// This will fail early if something is missing.
function check_requirements() {
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
    if (!defined('IA_HPHP_ENV')) {
        if (array_search('zip', $extensions) === false) {
            log_warn("zip extension missing.");
        }
        if (!function_exists("finfo_open")) {
            log_warn('finfo_open missing, falling back to mime_content_type.');
            if (!function_exists("mime_content_type")) {
                log_warn('mime_content_type missing, mime-types will ' .
                         'default to application/octet-stream.');
            }
        }
    }
    if (array_search('mbstring', $extensions) === false) {
        log_warn('mbstring extension missing. inline diff and ' .
                 'character normalisation will not be available.');
    }

    // Check for retarded php.ini settings.
    if (IA_HTTP_ENV && !defined('IA_HPHP_ENV')) {
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

/*
 * Checks whether the current connection is through the https request
 *
 * returns bool
 */
function is_connection_secure() {
    $https = getattr($_SERVER, 'HTTPS', null);

    if ($https == 'on' || $https == '1' ||
        getattr($_SERVER, 'HTTP_X_FORWARDED_PROTO') === 'https') {
        return true;
    }
    return false;
}

/*
 * Return information about the remote IP address. Useful for logging.
 * This isn't necessarily just an IP address. It might contain proxy
 * information when available.
 *
 * @return string
 */
function remote_ip_info() {
    $ip_address = getattr($_SERVER, 'HTTP_X_REAL_IP',
                          getattr($_SERVER, 'REMOTE_ADDR'));
    if ($ip_address && !is_valid_ip_address($ip_address)) {
        log_warn("Invalid IP address: {$ip_address}", true, 1);
    }
    // FIXME: Also validate XFF header.
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // X-Forwarded-For: client, proxy1, proxy2, ...
        $forwarded_for = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (strstr($forwarded_for, $ip_address)) {
            $ip_address = $forwarded_for;
            // Cloudflare + nginx real ip module results in X-Forwarded-For
            // headers of the form {ip}, {ip}
            $ip_address_list = explode(', ', $ip_address);
            if (count($ip_address_list) > 1 &&
                $ip_address_list[0] === $ip_address_list[1]) {
                unset($ip_address_list[0]);
                $ip_address = implode(', ', $ip_address_list);
            }
        } else {
            // The IP address should always be part of the XFF header
            $ip_address .= '; ' . $forwarded_for;
            log_warn("Invalid X-Forwarded-For header detected: {$ip_address}");
        }
    }
    return $ip_address;
}
