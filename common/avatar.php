<?php

require_once(Config::ROOT.'common/db/db.php');
require_once(Config::ROOT.'www/controllers/attachment.php');
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
 * Receives an uploaded avatar and returns errors if any.
 * @param  string  $temporary_name
 * @param  string  $filepath          The filepath where to copy the attachment
 * @param  string  $username
 * @return mixed   Error message or null on success
 */
function avatar_upload($temporary_name, $filepath, $username) {
  // Make sure that the file is an image.
  if (!Image::isImage($temporary_name)) {
    return 'Pentru avatar poți folosi imagini GIF, JPEG, PNG sau SVG.';
  }

  // write the file on disk.
  if (!move_uploaded_file($temporary_name, $filepath)) {
    return 'Nu am putut încărca fișierul.';
  }
  return null;
}
