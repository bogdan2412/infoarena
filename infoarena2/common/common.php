<?php

if (IA_DEVELOPMENT_MODE) {
    $execution_stats = array(
        'timestamp' => microtime(true),
        'queries' => 0,
        'log_copy' => '',
    );
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

// Checks if the referer is the same as the host
function http_referer_check() {
    $HTTP_REFERER = getattr($_SERVER, 'HTTP_REFERER');
    $HTTP_HOST = getattr($_SERVER, 'HTTP_HOST');
    return $HTTP_REFERER==null || substr($HTTP_REFERER, 0, (strlen($HTTP_HOST)+7)) == "http://".$HTTP_HOST;
}

// Check if a a variable is a whole number.
function is_whole_number($x) {
    return is_numeric($x) && $x == intval($x);
}

// tell if email address seems to be valid
function is_valid_email($email) {
    // email validation is trickier than it seems!
    // if you think you have a better regexp, beat this:
    // http://www.regular-expressions.info/email.html
    //
    // let's keep it simple
    return preg_match('/[^@]+@.+\..+/', $email);
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
    $res = is_page_name($page_name)
           && !preg_match('/(\/\/)|(\/$)|([A-Z])/', $page_name);
    return $res;
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
    return preg_match('/^[a-z0-9][a-z0-9_]*$/i', $round_id) && strlen($round_id) < 16;
}

// Check valid score names.
function is_score_name($score_name)
{
    return preg_match('/^[a-z0-9][a-z0-9_]*$/i', $score_name) && strlen($score_name) < 32;
}

// Tells whether $task_id is a valid task identifier
// Does not check existence.
function is_task_id($task_id) {
    return preg_match('/^[a-z0-9][a-z0-9_]*$/i', $task_id) && strlen($task_id) < 16;
}

// Get a file's mime type.
function get_mime_type($filename) {
    if (function_exists("finfo_open")) {
        // FIXME: cache.
        $finfo = finfo_open(FILEINFO_MIME);

        log_assert($finfo !== false,
                   'fileinfo is active but finfo_open() failed');

        $res = finfo_file($finfo, $filename);
        finfo_close($finfo);
        log_print('get_mime_type('.$filename.'): finfo yields '.$res);
        return $res;
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

// Resize 2D coordinates according to 'textual' instructions
// Given a (width, height) pair, resize it (compute new pair) according to
// resize instructions.
//
// Resize instructions may be:
// # example    # description
// 100x100      Keep aspect ratio, resize as to fit a 100x100 box.
//              Coordinates are not enlarged if they already fit the given box.
// @50x86       Ignore aspect ratio, resize to exactly 50x86.
// 50%          Scale dimensions; only integer percentages allowed.
// L100x100     Layout resize: same as 100x100 only it will enlarge coordinates
//              if coordinates already fit target box. Use this where layout
//              matters.
//
// Returns 2-element array: (width, height) or null if invalid format
function resize_coordinates($width, $height, $resize) {
    // 100x100 or @100x100 or L100x100
    if (preg_match('/^([\@L]?)([0-9]+)x([0-9]+)$/i', $resize, $matches)) {
        $flag = strtolower($matches[1]);
        $boxw = (float)$matches[2];
        $boxh = (float)$matches[3];

        if ('@' == $flag) {
            // exact fit, ignore aspect ratio
            return array($boxw, $boxh);
        }
        else {
            // keep aspect ratio

            $layout = ('l' == $flag);
            $ratio = 1.0;
            if ($width > $boxw || $layout) {
                $ratio = $boxw / $width;
            }
            if ($height * $ratio > $boxh) {
                $ratio = $boxh / $height;
            }

            return array(floor($ratio * $width), floor($ratio * $height));
        }
    }
    // zoom: 50%
    elseif (preg_match('/^([0-9]+)%$/', $resize, $matches)) {
        $ratio = (float)$matches[1] / 100;
        return array(floor($ratio * $width), floor($ratio * $height));
    }
    // invalid format
    else {
        return null;
    }
}

error_reporting(0xFFFF);

// All our logic is done in UTC, the sensible way.
if (function_exists("date_default_timezone_set")) {
    date_default_timezone_set("UTC");
}

?>
