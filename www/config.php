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
define("IA_REEVAL_MAXJOBS", 2048);

// maximum file size for user-submitted files - solutions to tasks
define("IA_SUBMISSION_MAXSIZE", 256*1024);

// maximum avatar file-size
define("IA_AVATAR_MAXSIZE", 400*1024);

date_default_timezone_set('GMT');

// Constrains and default value for pager display_rows.
define('IA_PAGER_DEFAULT_DISPLAY_ENTRIES', 50);
define('IA_PAGER_MAX_DISPLAY_ENTRIES', 250);
define('IA_PAGER_MIN_DISPLAY_ENTRIES', 3);
$IA_PAGER_DISPLAY_ENTRIES_OPTIONS = array(25, 50, 100, 250);

// Cache directory
define('IA_TEXTILE_CACHE_ENABLE', true);

// List of safe MIME types
// FIXME: add more?
$IA_SAFE_MIME_TYPES = [
    'image/bmp',
    'image/gif',
    'image/jpeg',
    'image/png',
    'image/svg+xml',
    'image/x-ms-bmp',
];

define('IA_USER_MAX_ARCHIVE_WAITING_JOBS', 1);
