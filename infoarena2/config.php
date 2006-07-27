<?php

/**
 * This is the central configuration file for info-arena 2.0
 * You have to modify this after you first do a svn checkout.
 */

/**
 * # info-arena root directory
 * This is the subversion checkout directory; include trailing slash.
 */
define("IA_ROOT", '/home/wickedman/devel/infoarena2/');
define("DB_HOST", '');
define("DB_NAME", 'infoarena2');
define("DB_USER", 'infoarena2');
define("DB_PASS", 'infoarena2');

/**
 * # absolute URLs to info-arena website and other web based tools
 * you probably want to leave these just as they are
 */
define("IA_URL_PREFIX", "/infoarena2/");
define("IA_URL_REWRITE", false);
define("IA_URL", "http://localhost" . IA_URL_PREFIX);
define("IA_URL_NEWSLETTER", "");    // blank disables feature

/**
 * # implicit configuration
 * No need to change below
 */
define  ("IA_ROOT_WWW", IA_ROOT . "www/");

?>
