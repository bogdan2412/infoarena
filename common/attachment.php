<?php
require_once(IA_ROOT_DIR . "common/db/attachment.php");

// Validates an attachment struct.
function attachment_validate($att) {
    $errors = array();

    // FIXME How to handle this?
    log_assert(is_array($att), "You didn't even pass an array");

    if (!is_normal_page_name(getattr($att, 'page', ''))) {
        $errors['page'] = 'Nume de pagina invalid.';
    }

    if (!is_attachment_name(getattr($att, 'name', ''))) {
        $errors['page'] = 'Nume de pagina invalid.';
    }

    if (!is_user_id(getattr($att, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid';
    }

    if (!is_string(getattr($att, 'mime_type', 'text/plain'))) {
        $errors['mime_type'] = 'Bad mime type';
    }

    // NOTE: missing timestamp is OK!!!
    // It stands for 'current moment'.
    if (!is_db_date(getattr($att, 'timestamp', db_date_format()))) {
        $errors['timestamp'] = 'Timestamp invalid.';
    }

    return $errors;
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

?>
