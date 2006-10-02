<?php

require('controllers/attachment.php');

// Resize 2D coordinates according to 'textual' instructions
// Given a (width, height) pair, resize it (compute new pair) according to
// resize instructions.
//
// Resize instructions format may be one of the following:
// # example    # description
// 100x100      keep aspect ratio, resize as to fit a 100x100 box
//              Coordinates are not enlarged if they already fit the given box.
// @100x100     keep aspect ratio, resize as to exactly fit a 100x100 box
//              Enlarge coordinates if necessary
// !50x86       ignore aspect ratio, resize to exactly 50x86
// 50%          scale dimensions. only integer percentages allowed
//
// Returns 2-element array: (width, height) or null if invalid format
function resize_coordinates($width, $height, $resize) {
    // remove @
    if (0 < strlen($resize) && $resize[0] == '@') {
        $enlarge = true;
        $resize = substr($resize, 1);
    }
    else {
        $enlarge = false;
    }

    $ratio = 1.0;

    // 100x100 or @100x100
    if (preg_match('/^([0-9]+)x([0-9]+)$/', $resize, $matches)) {
        $boxw = (float)$matches[1];
        $boxh = (float)$matches[2];

        if ($width > $boxw || $enlarge) {
            $ratio =  $boxw / $width;
        }
        if ($height * $ratio > $boxh) {
            $ratio *= $boxh / ($height * $ratio);
        }
    }
    // 50%
    elseif (preg_match('/^([0-9]+)%$/', $resize, $matches)) {
        $ratio = (float)$matches[1] / 100;
    }
    // !50x86
    elseif (preg_match('/^!([0-9]+)x([0-9]+)$/', $resize, $matches)) {
        $width = (float)$matches[1];
        $height = (float)$matches[2];
    }
    else {
        return null;
    }

    return array(floor($ratio * $width), floor($ratio * $height));
}

// download attachment as resized image
function controller_attachment_resized_img($page_name, $file_name, $resize) {
    if (!$resize) {
        // no resize information: issue a regular file download
        controller_attachment_download($page_name, $file_name);
    }

    $attach = try_attachment_get($page_name, $file_name);
    $real_name = attachment_get_filepath($attach);

    $ret = getimagesize($real_name);
    if (false === $ret) {
        flash_error('Fisierul specificat nu este recunoscut ca o imagine.');
        redirect(url($page_name));
    }
    list($img_width, $img_height, $img_type, $img_attr) = $ret;

    // validate resize instructions & compute new dimensions
    $newcoords = resize_coordinates($img_width, $img_height, $resize);
    if (is_null($newcoords)) {
        // invalid resize information
        flash_error('Instructiuni de redimensionare invalide. Iata niste exemple corecte: 100x100'
                    . ' | @100x100 | !55x131 | 50%');
        redirect(url($page_name));
    }
    $new_width = $newcoords[0];
    $new_height = $newcoords[1];

    // put some constraints here for security

    // actual image resizing
    // FIXME: optimize code not to use output buffering. Image should be
    // streamed directly to user agent.
    ob_start();
    switch ($img_type) {
        case IMAGETYPE_GIF:
            // NOTE: animated GIFs become static. Only the first frame is saved
            // Seems like a good thing anyway
            $im = imagecreatefromgif($real_name);
            $im_resized = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($im_resized, $im, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
            imagegif($im_resized);
            break;

        case IMAGETYPE_JPEG:
            $im = imagecreatefromjpeg($real_name);
            $im_resized = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($im_resized, $im, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
            imagejpeg($im_resized);
            break;

        case IMAGETYPE_PNG:
            $im = imagecreatefrompng($real_name);
            $im_resized = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($im_resized, $im, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
            imagepng($im_resized);
            break;

        default:
            ob_end_clean();
            // unsupported image type
            flash_error('Imaginea atasata (desi a fost recunoscuta ca fisier de tip imagine) nu poate fi redimensionata. '
                        . 'Incercati alt format.');
            redirect(url($page_name));
    }
    $buffer = ob_get_contents();
    ob_end_clean();

    // HTTP headers
    header("Content-Type: " . image_type_to_mime_type($img_type));
    header("Content-Disposition: inline; filename=" . urlencode($file_name) . ";");
    // FIXME: strlen() is supposed to be binary safe but some say it will be shadowed
    // by mb_strlen() and treat strings as unicode by default. What is the alternative?
    header('Content-Length: ', strlen($buffer));

    // serve content
    echo $buffer;
    die();
}

?>
