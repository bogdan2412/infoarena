<?php

require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
require_once(IA_ROOT_DIR.'common/db/attachment.php');
require_once(IA_ROOT_DIR.'common/cache.php');

// download attachment as resized image
// see resize_coordinates() from utilities.php for detailed informations about
// valid $resize instructions
function controller_attachment_resized_img($page_name, $file_name, $resize) {
    if (!$resize) {
        // no resize information: issue a regular file download
        controller_attachment_download($page_name, $file_name);
    }

    // check if image exists
    $attach = attachment_get($file_name, $page_name);
    $found = !is_null($attach);
    if ($found) {
        $real_name = attachment_get_filepath($attach);
        $found = file_exists($real_name);
    }
    // if image was not found we display a placeholder image
    if (!$found) {
        $page_name = 'template/infoarena';
        $file_name = 'noimage';
        $attach = attachment_get($file_name, $page_name);
        log_assert($attach);
        $real_name = attachment_get_filepath($attach);
    }
    // check permission to download file
    if (!identity_can('attach-download', $attach)) {
        die_http_error();
    }

    // Abort if client has up-to-date version.
    $mtime = @filemtime($real_name);
    http_cache_check($mtime);

    // Get image stats.
    $ret = getimagesize($real_name);
    if (false === $ret) {
        die_http_error(500, "Bad image format.");
    }
    list($img_width, $img_height, $img_type, $img_attr) = $ret;

    // validate resize instructions & compute new dimensions
    $newcoords = resize_coordinates($img_width, $img_height, $resize);
    if (is_null($newcoords)) {
        die_http_error(500, "Bad url coords.");
    }
    $new_width = $newcoords[0];
    $new_height = $newcoords[1];

    // put some constraints here for security
    if ($new_width > IA_IMAGE_RESIZE_MAX_WIDTH || $new_height > IA_IMAGE_RESIZE_MAX_HEIGHT) {
        die_http_error(500, "Bad image size.");
    }

    // query image cache for existing resampled image
    if (IA_IMAGE_CACHE_ENABLE) {
        $cache_id = $attach['id'] . '_' . $resize;
        if (disk_cache_has($cache_id, $mtime)) {
            disk_cache_serve($cache_id, $file_name, image_type_to_mime_type($img_type));
            log_error("Should not return");
        }
    }

    // Actual image resizing
    // Image is placed in output buffer, cached, then flushed.
    ob_start();
    switch ($img_type) {
        case IMAGETYPE_GIF:
            // NOTE: animated GIFs become static. Only the first frame is saved
            // Seems like a good thing anyway
            $im = imagecreatefromgif($real_name);
            $im_resized = imagecreate($new_width, $new_height);
            // reset palette and transparent color to that of the original file
            $trans_col = imagecolortransparent($im);
            imagepalettecopy($im_resized, $im);
            imagefill($im_resized, 0, 0, $trans_col);
            imagecolortransparent($im_resized, $trans_col);
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
            // turn off the alpha blending to keep the alpha channel
            imagealphablending($im_resized, false);
            // allocate transparent color
            $col = imagecolorallocatealpha($im_resized, 0, 0, 0, 127);
            // fill the image with the new color
            imagefilledrectangle($im_resized, 0, 0, $new_width, $new_height, $col);
            imagecopyresampled($im_resized, $im, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height);
            imagesavealpha($im_resized, true);
            imagepng($im_resized);
            break;

        default:
            ob_end_clean();
            // unsupported image type
            die_http_error(500, "Unsupported image type");
    }

    // Store in cache, if enabled
    if (IA_IMAGE_CACHE_ENABLE) {
        disk_cache_set($cache_id, ob_get_contents());
    }

    // HTTP headers
    header("Content-Type: " . image_type_to_mime_type($img_type));
    header("Content-Disposition: inline; filename=" . urlencode($file_name) . ";");
    // WARNING: strlen() is supposed to be binary safe but some say it may 
    // be shadowed by mb_strlen() and treat strings as unicode by default,
    // thus reporting invalid lengths for random binary buffers.
    // What is the alternative?
    header('Content-Length: ', ob_get_length());

    ob_end_flush();
    die();
}

?>
