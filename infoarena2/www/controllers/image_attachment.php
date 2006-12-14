<?php

require_once(IA_ROOT.'www/controllers/attachment.php');
require_once(IA_ROOT.'common/db/attachment.php');

// download attachment as resized image
// see resize_coordinates() from utilities.php for detailed informations about
// valid $resize instructions
function controller_attachment_resized_img($page_name, $file_name, $resize) {
    if (!$resize) {
        // no resize information: issue a regular file download
        controller_attachment_download($page_name, $file_name);
    }

    // check if image exists
    $found = true;
    $attach = attachment_get($file_name, $page_name);
    if (!$attach) {
        $found = false;
    }
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
    if ($new_width > IMAGE_MAX_WIDTH || $new_height > IMAGE_MAX_HEIGHT) {
        die_http_error(500, "Bad image size.");
        redirect(url($page_name));
    }

    // query image cache for existing resampled image
    if (IMAGE_CACHE_ENABLE) {
        $cache_fn = imagecache_query($attach, $resize);

        if (null !== $cache_fn) {
            // cache has it
            serve_attachment($cache_fn, $file_name, image_type_to_mime_type($img_type));
            // function doesn't return
        }
    }

    // actual image resizing
    // FIXME: optimize code not to use output buffering. Image should be
    // streamed directly to user agent.
    ob_start();
    log_print("Resizing image {$page_name} to {$resize}");
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
    $buffer = ob_get_contents();
    ob_end_clean();

    // cache resample
    if (IMAGE_CACHE_ENABLE) {
        imagecache_save($attach['id'], $resize, $buffer);
    }

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

// Tells whether there is a resampled (resized according to $resize instructions) and
// up-to-date version of image attachment $attach_id.
//
// Returns
//  - disk file name of the resampled image so it can be served via serve_attachment()
//  - null if no such cached version exists
function imagecache_query($attach, $resize) {
    // get disk file paths
    $fn_cache = imagecache_filename($attach['id'], $resize);
    $fn_source = attachment_get_filepath($attach);

    // open files
    $fp_cache = @fopen($fn_cache, 'rb');
    if (!$fp_cache) {
        return null;
    }
    $fp_source = @fopen($fn_source, 'rb');
    if (!$fp_source) {
        return null;
    }

    // stat
    $stat_source = @fstat($fp_source);
    $stat_cache = @fstat($fp_cache);

    // close files
    fclose($fp_cache);
    fclose($fp_source);

    // decide
    if ($stat_source['mtime'] > $stat_cache['mtime']) {
        // cache is older than source
        return null;
    }
    else {
        // cache is up-to-date
        return $fn_cache;
    }
}

// Inserts resampled version of attachment $attach_id into image cache.
// $buffer is the actual binary file contents of the resampled image.
//
// Returns boolean whether caching succeeded. File will not be cached if
// image cache exceeds allowed quota.
function imagecache_save($attach_id, $resize, $buffer) {
    if (imagecache_usage() > IMAGE_CACHE_QUOTA) {
        // cache is full
        log_warn('Image cache is full.');
        return false;
    }

    $filename = imagecache_filename($attach_id, $resize);
    $ret = file_put_contents($filename, $buffer, LOCK_EX);
    if (false === $ret) {
        log_error('IMAGE_CACHE: Could not create file ' . $filename);
        return false;
    }

    log_print("Saved resampled image {$filename} {$resize} in image cache");

    return true;
}

// Returns current disk size of image cache.
function imagecache_usage() {
    // scan all files in image cache directory
    $nodes = scandir(IMAGE_CACHE_DIR);
    $files = array();
    foreach ($nodes as $node) {
        if (!is_dir($node)) {
            $files[] = $node;
        }
    }

    // sum up file size
    $total = 0;
    foreach ($files as $file) {
        $fsize = filesize(IMAGE_CACHE_DIR . $file);
        if (false === $fsize) {
            log_warn('IMAGE_CACHE: Could not determine file size of resampled image ' . IMAGE_CACHE_DIR . $file);
        }
        $total += $fsize;
    }

    return $total;
}

// Returns absolute file path for a (possibly inexistent) cached resampled image.
function imagecache_filename($attach_id, $resize) {
    return IMAGE_CACHE_DIR . $attach_id . '_' . $resize;
}

?>
