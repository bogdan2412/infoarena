<?php

require_once(IA_ROOT_DIR.'common/db/db.php');
require_once(IA_ROOT_DIR.'www/controllers/attachment.php');
require_once(IA_ROOT_DIR.'www/controllers/image_attachment.php');
require_once(IA_ROOT_DIR.'common/db/attachment.php');
require_once(IA_ROOT_DIR.'www/controllers/account_validator.php');
require_once(IA_ROOT_DIR.'common/common.php');
require_once(IA_ROOT_DIR.'common/attachment.php');

/**
 * It takes an avatar image file given by it's filepath and resizes it in the
 * sizes necessary on the site.
 *
 * @param  string  $filepath
 * @param  array   $image_info
 * @param  string  $new_filename
 */
function avatar_cache_resized($filepath, $image_info, $new_filename) {
    $resize_sizes = array('L16x16' => 'tiny/', 'L32x32' => 'small/',
            'L50x50' => 'normal/' , '75x75'=> 'forum/', '150x150' => 'big/');

    // Copying the original image
    copy($filepath, IA_AVATAR_FOLDER.'full/'.$new_filename);

    list($image_width, $image_height, $image_type, $image_attribute) =
            $image_info;

    foreach ($resize_sizes as $resize_size => $resize_folder) {
        $new_image_info = resize_coordinates($image_width,
                $image_height, $resize_size);

        // resizing
        image_resize($image_info, $filepath, $new_image_info,
                IA_AVATAR_FOLDER.$resize_folder.$new_filename);
    }
}

?>
