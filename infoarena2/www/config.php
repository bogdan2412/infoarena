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

// maximum attachment size for wiki pages
define("IA_ATTACH_MAXSIZE", 20*1024*1024);

// directory where to upload files AND from where users download them
define("IA_ATTACH_DIR", "/tmp/");

?>
