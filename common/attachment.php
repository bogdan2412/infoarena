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
    if (!is_db_date(getattr($att, 'timestamp', db_date_format()))) {
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
    $matches = null;
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

/**
 * Resizes an image whose filepath is given by the parameters into a new
 * location specified by the other parameters
 *
 * Returns whether or not the image has been successfully resized
 * Note that it returns false if the image doesn't have a known file-type
 *
 * The $new_filepath parameter if ommited or given as null will result in the
 * image being outputted directly to the client
 *
 * @param  array   $image_info         An array containing information about the
 *                                     original image: width, height, mime-type
 * @param  string  $filepath
 * @param  array   $new_image_info     An array containing the new width and
 *                                     height
 * @param  string  $new_filepath
 * @return bool
 */
function image_resize($image_info, $filepath, $new_image_info,
        $new_filepath = null) {
    list($image_width, $image_height, $image_type, $image_attribute) = $image_info;
    list($new_image_width, $new_image_height) = $new_image_info;

    switch ($image_type) {
        case IMAGETYPE_GIF:
            // NOTE: animated GIFs become static. Only the first frame is saved
            // Seems like a good thing anyway
            $image = imagecreatefromgif($filepath);
            $image_resized = imagecreate($new_image_width, $new_image_height);
            // reset palette and transparent color to that of the original file
            $trans_col = imagecolortransparent($image);
            imagepalettecopy($image_resized, $image);
            if ($trans_col != -1) {
                imagefill($image_resized, 0, 0, $trans_col);
            }
            imagecolortransparent($image_resized, $trans_col);
            imagecopyresampled($image_resized, $image, 0, 0, 0, 0,
                    $new_image_width, $new_image_height, $image_width,
                    $image_height);

            if ($new_filepath != null) {
                $ret = imagegif($image_resized, $new_filepath);
            } else {
                $ret = imagegif($image_resized);
            }
            imagedestroy($image);
            imagedestroy($image_resized);
            return $ret;

        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($filepath);
            $image_resized = imagecreatetruecolor($new_image_width,
                    $new_image_height);
            imagecopyresampled($image_resized, $image, 0, 0, 0, 0,
                    $new_image_width, $new_image_height, $image_width,
                    $image_height);

            if ($new_filepath != null) {
                $ret = imagejpeg($image_resized, $new_filepath);
            } else {
                $ret = imagejpeg($image_resized);
            }
            imagedestroy($image);
            imagedestroy($image_resized);
            return $ret;

        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($filepath);
            $image_resized = imagecreatetruecolor($new_image_width,
                    $new_image_height);
            // turn off the alpha blending to keep the alpha channel
            imagealphablending($image_resized, false);
            // allocate transparent color
            $col = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
            // fill the image with the new color
            imagefilledrectangle($image_resized, 0, 0, $new_image_width,
                    $new_image_height, $col);
            imagecopyresampled($image_resized, $image, 0, 0, 0, 0,
                    $new_image_width, $new_image_height, $image_width,
                    $image_height);
            imagesavealpha($image_resized, true);
            imagecolordeallocate($image_resized, $col);
            if ($new_filepath != null) {
                $ret = imagepng($image_resized, $new_filepath);
            } else {
                $ret = imagepng($image_resized);
            }
            imagedestroy($image);
            imagedestroy($image_resized);
            return $ret;

        default:
            // unsupported image type
            return false;
    }
}
