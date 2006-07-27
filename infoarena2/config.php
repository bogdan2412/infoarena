<?php

/**
 * This is the central configuration file for info-arena 2.0
 * You have to modify this after you first do a svn checkout.
 */

/**
 * # info-arena root directory
 * This is the subversion checkout directory; include trailing slash.
 */
define("IA_ROOT", '--edit--me--');
define("DB_HOST", '--edit--me--');
define("DB_NAME", '--edit--me--');
define("DB_USER", '--edit--me--');
define("DB_PASS", '--edit--me--');

/**
 * # absolute URLs to info-arena website and other web based tools
 * you probably want to leave these just as they are
 */
define("IA_URL_INFO",           "http://localhost/infoarena2/");
define("IA_URL_NEWSLETTER", "");    // blank disables feature

/**
 * # implicit configuration
 * No need to change below
 */
define  ("IA_ROOT_WWW", IA_ROOT . "www/");

?>
