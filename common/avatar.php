<?php

require_once(Config::ROOT.'common/db/db.php');
require_once(Config::ROOT.'www/controllers/attachment.php');
require_once(Config::ROOT.'www/controllers/image_attachment.php');
require_once(Config::ROOT.'common/db/attachment.php');
require_once(Config::ROOT.'www/controllers/account_validator.php');
require_once(Config::ROOT.'common/common.php');
require_once(Config::ROOT.'www/config.php');
require_once(Config::ROOT.'common/attachment.php');

/**
 * Returns whether the attachment of the given page is an avatar attachment
 * @param  string  $attachment_name
 * @param  string  $page_name
 * @return bool
 */
function is_avatar_attachment($attachment_name, $page_name) {
  $matches = get_page_user_name($page_name);

  if ($attachment_name === 'avatar' && $matches) {
    return true;
  }

  return false;
}

/**
 * Resizes a newly uploaded avatar and returns errors if any
 * @param  string  $temporary_name
 * @param  string  $filepath          The filepath where to copy the attachment
 * @param  string  $username
 * @return mixed   Error message or null on success
 */
function avatar_update($temporary_name, $filepath, $username) {
  // resize the avatar if it has a correct mime-type
  $avatar_mime_types = array('image/gif', 'image/jpeg', 'image/png');
  $image_info = getimagesize($temporary_name);
  if (!in_array($image_info['mime'], $avatar_mime_types)) {
    return 'Fișierul nu este o imagine acceptată pe site. ' .
      'Utilizați doar imagini GIF, JPEG sau PNG.';
  }

  // write the file on disk.
  if (!move_uploaded_file($temporary_name, $filepath)) {
    return 'Fișierul nu a putut fi încărcat pe server.';
  }
  // resize the avatar
  avatar_cache_resized($filepath, $image_info, "a".$username);
  return null;
}

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
                        'L50x50' => 'normal/' , '150x150' => 'big/');

  // Hardlink / Copy the original image
  $new_filepath = IA_AVATAR_FOLDER . 'full/' . $new_filename;
  if (is_file($new_filepath) || is_link($new_filepath)) {
    unlink($new_filepath);
  }
  if (!link($filepath, $new_filepath)) {
    if (!copy($filepath, $new_filepath)) {
      log_error('Unable to copy user avatar into avatar folder');
    }
  }

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

/**
 * Delete's an user avatar, the rest is done from the attachment page
 *
 * @param  string  $username
 */
function avatar_delete($username) {
  // $username is lowercased by normalize_page_name(). Get the real one.
  $user = user_get_by_username($username);
  $username = $user['username'];

  $resize_folders = array('tiny/', 'small/', 'normal/', 'big/');

  // Unlink the hardlinked full-sized image
  $filepath = IA_AVATAR_FOLDER . 'full/a' . $username;
  if (is_file($filepath) || is_link($filepath)) {
    unlink($filepath);
  }

  // Delete the resized ones
  foreach ($resize_folders as $resize_folder) {
    $filepath = IA_AVATAR_FOLDER . $resize_folder . 'a'
      . $username;
    if (is_file($filepath) || is_link($filepath)) {
      unlink($filepath);
    }
  }
}
