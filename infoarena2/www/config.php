<?php
/**
 * This file contains configuration settings specific for the infoarena
 * WEBSITE.
 *
 * Please note that the "big" configuration file (residing one directory up)
 * is meant to keep settings that are common accross all infoarena
 * applications.
 * 
 * This file has some decent defaults.
 */

// Include main config.
require_once("../config.php");

// maximum attachment size for wiki pages
define("IA_ATTACH_MAXSIZE", 20*1024*1024);

// directory where to upload files AND from where users download them
define("IA_ATTACH_DIR", "/tmp/");

// maximum file size for user-submitted files - solutions to tasks
define("IA_SUBMISSION_MAXSIZE", 256*1024);

// maximum avatar file-size
define("IA_AVATAR_MAXSIZE", 200*1024);

// maximum avatar dimensions
define("IA_AVATAR_WIDTH", 100);
define("IA_AVATAR_HEIGHT", 100);

// Number of news items to place on one page.
define('IA_MAX_NEWS', 10);

// Number of items in a RSS feed
define('IA_MAX_FEED_ITEMS', 15);
date_default_timezone_set('GMT');

// Default number of rows to display in diferent tables
define('IA_DEFAULT_ROWS_PER_PAGE', 20);

// mail sender
define("IA_MAIL_SENDER_NO_REPLY", 'info-arena <no-reply@infoarena.ro>');

// Maximum number of recursive includes in the wiki.
define('IA_MAX_RECURSIVE_INCLUDES', 5);

// Boolean whether to display SQL queries and helpful debug messages
// when encountering a SQL error.
//
// :WARNING: Disable this option when uploading the website to a production
// environment! Telling poeple too much about your database is rarely a good
// thing.
define("IA_SQL_TRACE", true);

// Image resampling
//  - constraints for image resampling
define("IMAGE_MAX_WIDTH", 800);
define("IMAGE_MAX_HEIGHT", 800);
//  - whether to enable the image cache (avoid resizing the same image twice)
define("IMAGE_CACHE_ENABLE", true);
//  - where to store image cache (resampled versions of the normal image attachments)
//    Feel free to empty the cache directory at any time
define("IMAGE_CACHE_DIR", "/tmp/");
//  - maximum directory size for image cache (bytes). When directory exceeds quota,
//    image resamples are not cached any more but computed & served on-the-fly
define("IMAGE_CACHE_QUOTA", 32 * 1024 * 1024); // (bytes please)

?>
