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

// client-side HTTP cache
define("IA_CLIENT_CACHE_ENABLE", true);
define("IA_CLIENT_CACHE_AGE", 604800);

// maximum attachment size for wiki pages
define("IA_ATTACH_MAXSIZE", 64*1024*1024);

// maximum jobs to reeval
define("IA_REEVAL_MAXJOBS", 512);

// maximum file size for user-submitted files - solutions to tasks
define("IA_SUBMISSION_MAXSIZE", 256*1024);

// maximum avatar file-size
define("IA_AVATAR_MAXSIZE", 400*1024);

// Number of items in a RSS feed
define('IA_MAX_FEED_ITEMS', 15);
date_default_timezone_set('GMT');

// Constrains and default value for pager display_rows.
define('IA_PAGER_DEFAULT_DISPLAY_ENTRIES', 50);
define('IA_PAGER_MAX_DISPLAY_ENTRIES', 250);
define('IA_PAGER_MIN_DISPLAY_ENTRIES', 3);
$IA_PAGER_DISPLAY_ENTRIES_OPTIONS = array(25, 50, 100, 250);

// User date formatting.
// Everything in the database is UTC.
// Date formatting for the user is done in www/format/format.php
define('IA_DATE_DEFAULT_TIMEZONE', 'Europe/Bucharest');
define('IA_DATE_DEFAULT_FORMAT', '%e %B %Y %H:%M:%S');

// mail sender
define("IA_MAIL_SENDER_NO_REPLY", 'infoarena <no-reply@infoarena.ro>');

// Maximum number of recursive includes in the wiki.
define('IA_MAX_RECURSIVE_INCLUDES', 5);

// Cache directory
define('IA_CACHE_ENABLE', true);
define('IA_IMAGE_CACHE_ENABLE', true);
define('IA_TEXTILE_CACHE_ENABLE', true);
// FIXME: proper cleaning mechanism.
define('IA_CACHE_SIZE', 256 * 1024 * 1024);

// Image resampling
//  - constraints for image resampling
define("IA_IMAGE_RESIZE_MAX_WIDTH", 800);
define("IA_IMAGE_RESIZE_MAX_HEIGHT", 800);

// Textblock name for sidebar ad
define("IA_SIDEBAR_PAGE", "sidebar-ad");

// Textblock name for blog sidebar
define("IA_BLOG_SIDEBAR", "blog-sidebar");

// LaTeX support
define("IA_LATEX_ENABLE", !IA_DEVELOPMENT_MODE);

// List of safe MIME types
// FIXME: add more?
$IA_SAFE_MIME_TYPES = array('image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/x-ms-bmp');

?>
