<?php

// info-arena
// Configure your SMF installation by editing ALL --edit-me-- fields

// ATTENTION !!!
//
// SMF stores some settings in the database. Use the setup script.
// This thing gets all it's settings from the main config.php,
// There's no need for a .sample

// open main info-arena config
require_once("../config.php");

// Webmaster email (e-mail where it sends reports in case shit)
// Also serves as address to send emails from
$webmaster_email = 'no-reply@infoarena.ro';

// Decent defaults below (edit only if you get bored)
// ---------------------------------------------------------------

// The *absolute* disk path to the forum's folder. No trailing slash
$boarddir = IA_ROOT_DIR . 'smf';

// Maintenance
// Note: If $maintenance is set to 2, the forum will be unusable!
// Change it to 0 to fix it.
$maintenance = 0;
$mtitle = 'Maintenance Mode';
$mmessage = 'Okay faithful users...we\'re attempting to restore an '
            .'older backup of the database...news will be posted once '
            .'we\'re back!';

// Forum
$mbname = 'infoarena';
$language = 'romanian-utf8';

// URL to info-arena website
$infoarenaurl = IA_URL;

// URL to board
$boardurl = IA_SMF_URL;

// Name of the cookie to set for authentication
// NOTE: This is never used
$cookiename = 'SMFCookie3210';

// Database Info
$db_server = IA_DB_HOST;
$db_name = IA_DB_NAME;
$db_user = IA_DB_USER;
$db_passwd = IA_DB_PASS;
$db_prefix = IA_SMF_DB_PREFIX;
$db_persist = 0;
$db_error_send = 1;


// Directories/Files
$sourcedir = $boarddir.'/Sources';

// Error-Catching (please don't edit)
$db_last_error = 0;

// Make sure the paths are correct... at least try to fix them.
if (!file_exists($boarddir) && file_exists(dirname(__FILE__) . '/agreement.txt'))
    $boarddir = dirname(__FILE__);
if (!file_exists($sourcedir) && file_exists($boarddir . '/Sources'))
    $sourcedir = $boarddir . '/Sources';

