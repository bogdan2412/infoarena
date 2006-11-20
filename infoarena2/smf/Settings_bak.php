<?php

// info-arena
// Configure your SMF installation by editing ALL --edit-me-- fields


// ATTENTION !!!
//
// SMF MUST also be configured from its administration panel (settings
// are stored in database).
//
// Configure the settings from this file and then login using
//      user: SMFAdmin;  pass: faranumar
// and finish the configuration.




/******************************************************************************
* Settings.php                                                                *
*******************************************************************************
* SMF: Simple Machines Forum                                                  *
* Open-Source Project Inspired by Zef Hemel (zef@zefhemel.com)                *
* =========================================================================== *
* Software Version:           SMF 1.1 RC3                                     *
* Software by:                Simple Machines (http://www.simplemachines.org) *
* Copyright 2001-2006 by:     Lewis Media (http://www.lewismedia.com)         *
* Support, News, Updates at:  http://www.simplemachines.org                   *
*******************************************************************************
* This program is free software; you may redistribute it and/or modify it     *
* under the terms of the provided license as published by Lewis Media.        *
*                                                                             *
* This program is distributed in the hope that it is and will be useful,      *
* but WITHOUT ANY WARRANTIES; without even any implied warranty of            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                        *
*                                                                             *
* See the "license.txt" file for details of the Simple Machines license.      *
* The latest version can always be found at http://www.simplemachines.org.    *
******************************************************************************/

########## Maintenance ##########
# Note: If $maintenance is set to 2, the forum will be unusable!  Change it to 0 to fix it.
$maintenance = 0;		# Set to 1 to enable Maintenance Mode, 2 to make the forum untouchable. (you'll have to make it 0 again manually!)
$mtitle = 'Maintenance Mode';		# Title for the Maintenance Mode message.
$mmessage = 'Okay faithful users...we\'re attempting to restore an older backup of the database...news will be posted once we\'re back!';		# Description of why the forum is in maintenance mode.

########## Forum Info ##########
$mbname = 'info-arena';		# The name of your forum.
$language = 'romanian-utf8';		# The default language file set for the forum.

// example: http://localhost/smf_infoarena
$infoarenaurl = 'http://localhost/infoarena';		# URL to info-arena website
$boardurl = 'http://localhost/iasmf';		# URL to your forum's folder. (without the trailing /!)
$webmaster_email = 'strat.cristian@gmail.com';		# Email address to send emails from. (like noreply@yourdomain.com.)
$cookiename = 'SMFCookie360';		# Name of the cookie to set for authentication.

########## Database Info ##########
$db_server = 'localhost';
$db_name = 'infoarena2_dev';
$db_user = 'root';
$db_passwd = '';
$db_prefix = 'ia_smf_';
$db_persist = 0;
$db_error_send = 1;

########## Directories/Files ##########
# Note: These directories do not have to be changed unless you move things.

// example: /home/alexandru/devel/infoarena2/smf
// The absolute path to the forum's folder. (not just '.'!)
$boarddir = '/home/wickedman/devel/infoarena/smf';		# The absolute path to the forum's folder. (not just '.'!)

// (no need to edit)
$sourcedir = '/home/wickedman/devel/infoarena/smf/Sources';		# Path to the Sources directory.

########## Error-Catching ##########
# Note: You shouldn't touch these settings.
$db_last_error = 1163299824;


# Make sure the paths are correct... at least try to fix them.
if (!file_exists($boarddir) && file_exists(dirname(__FILE__) . '/agreement.txt'))
	$boarddir = dirname(__FILE__);
if (!file_exists($sourcedir) && file_exists($boarddir . '/Sources'))
	$sourcedir = $boarddir . '/Sources';

$db_character_set = 'utf8';
?>
