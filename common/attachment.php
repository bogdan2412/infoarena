<?php
require_once(Config::ROOT . "common/db/attachment.php");

// Validates an attachment struct.
function attachment_validate($att) {
    $errors = array();

    // FIXME How to handle this?
    log_assert(is_array($att), "You didn't even pass an array");

    if (!is_normal_page_name(getattr($att, 'page', ''))) {
        $errors['page'] = 'Nume de pagină invalid.';
    }

    if (!is_attachment_name(getattr($att, 'name', ''))) {
        $errors['page'] = 'Nume de pagină invalid.';
    }

    if (!is_user_id(getattr($att, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid.';
    }

    if (!is_string(getattr($att, 'mime_type', 'text/plain'))) {
        $errors['mime_type'] = 'Bad mime type.';
    }

    // NOTE: missing timestamp is OK!!!
    // It stands for 'current moment'.
    if (!is_db_date(getattr($att, 'timestamp', Time::formatMillis()))) {
        $errors['timestamp'] = 'Timestamp invalid.';
    }

    return $errors;
}

// Get a file's mime type.
function get_mime_type($filename) {
    if (function_exists("finfo_open")) {
        // FIXME: cache.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        log_assert($finfo !== false,
                   'fileinfo is active but finfo_open() failed');

        $res = finfo_file($finfo, $filename);
        finfo_close($finfo);
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

function is_textfile($mime_type) {
    return substr($mime_type, 0, 5) == "text/";
}

function dos_to_unix($file_path) {
    system("dos2unix ".$file_path);
}

function is_grader_testfile($file_name) {
    $pattern = '/^grader_test(\d)*\.(ok|in)$/';
    if (preg_match($pattern, $file_name) == false) {
        return false;
    } else {
        return true;
    }
}

function is_problem_page($page_name) {
    $pattern = '/^problema\/' . IA_RE_TASK_ID . '$/';
    if (preg_match($pattern, $page_name) == false) {
        return false;
    } else {
        return true;
    }
}

function add_ending_newline($file_path) {
    $content = file_get_contents($file_path);
    if ($content[strlen($content) - 1] != "\n") {
        $content .= "\n";
        file_put_contents($file_path, $content);
        return true;
    }
    return false;
}
