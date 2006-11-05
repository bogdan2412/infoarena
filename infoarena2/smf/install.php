<?php
/******************************************************************************
* install.php                                                                 *
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

$GLOBALS['current_smf_version'] = '1.1 RC3';

$GLOBALS['required_php_version'] = '4.1.0';
$GLOBALS['required_mysql_version'] = '3.23.28';

// Initialize everything and load the language files.
initialize_inputs();
load_lang_file();

// Don't have PHP support, do you?
// ><html dir="ltr"><head><title>Error!</title></head><body>Sorry, this installer requires PHP!<div style="display: none;">

header('Content-Type: text/html; charset=' . (isset($txt['lang_character_set']) ? $txt['lang_character_set'] : 'ISO-8859-1'));

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"', !empty($txt['lang_rtl']) ? ' dir="rtl"' : '', '>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=', isset($txt['lang_character_set']) ? $txt['lang_character_set'] : 'ISO-8859-1', '" />
		<title>', $txt['smf_installer'], '</title>
		<script language="JavaScript" type="text/javascript" src="Themes/default/script.js"></script>
		<style type="text/css">
			body
			{
				background-color: #E5E5E8;
				margin: 0px;
				padding: 0px;
			}
			body, td
			{
				color: #000000;
				font-size: small;
				font-family: verdana, sans-serif;
			}
			div#header
			{
				background-image: url(Themes/default/images/catbg.jpg);
				background-repeat: repeat-x;
				background-color: #88A6C0;
				padding: 22px 4% 12px 4%;
				color: white;
				font-family: Georgia, serif;
				font-size: xx-large;
				border-bottom: 1px solid black;
				height: 40px;
			}
			div#content
			{
				padding: 20px 30px;
			}
			div.error_message
			{
				border: 2px dashed red;
				background-color: #E1E1E1;
				margin: 1ex 4ex;
				padding: 1.5ex;
			}
			div.panel
			{
				border: 1px solid gray;
				background-color: #F6F6F6;
				margin: 1ex 0;
				padding: 1.2ex;
			}
			div.panel h2
			{
				margin: 0;
				margin-bottom: 0.5ex;
				padding-bottom: 3px;
				border-bottom: 1px dashed black;
				font-size: 14pt;
				font-weight: normal;
			}
			div.panel h3
			{
				margin: 0;
				margin-bottom: 2ex;
				font-size: 10pt;
				font-weight: normal;
			}
			form
			{
				margin: 0;
			}
			td.textbox
			{
				padding-top: 2px;
				font-weight: bold;
				white-space: nowrap;
				padding-', empty($txt['lang_rtl']) ? 'right' : 'left', ': 2ex;
			}
		</style>
	</head>
	<body>
		<div id="header">
			<a href="http://www.simplemachines.org/" target="_blank"><img src="Themes/default/images/smflogo.gif" style="width: 258px; float: ', empty($txt['lang_rtl']) ? 'right' : 'left', ';" alt="Simple Machines" border="0" /></a>
			<div title="Moogle Express!">', $txt['smf_installer'], '</div>
		</div>
		<div id="content">';

if (function_exists('doStep' . $_GET['step']))
	call_user_func('doStep' . $_GET['step']);

echo '
		</div>
	</body>
</html>';

function initialize_inputs()
{
	// Turn off magic quotes runtime and enable error reporting.
	@set_magic_quotes_runtime(0);
	error_reporting(E_ALL);

	// Fun.  Low PHP version...
	if (!isset($_GET))
	{
		$GLOBALS['_GET']['step'] = 0;
		return;
	}

	if (!isset($_GET['obgz']))
	{
		ob_start();

		if (@ini_get('session.save_handler') == 'user')
			@ini_set('session.save_handler', 'files');
		if (function_exists('session_start'))
			@session_start();
	}
	else
	{
		ob_start('ob_gzhandler');

		if (@ini_get('session.save_handler') == 'user')
			@ini_set('session.save_handler', 'files');
		session_start();

		if (!headers_sent())
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>', $_GET['pass_string'], '</title>
	</head>
	<body style="background-color: #D4D4D4; margin-top: 16%; text-align: center; font-size: 16pt;">
		<b>', $_GET['pass_string'], '</b>
	</body>
</html>';
		exit;
	}

	// Add slashes, as long as they aren't already being added.
	if (get_magic_quotes_gpc() == 0)
	{
		foreach ($_POST as $k => $v)
			$_POST[$k] = addslashes($v);
	}

	// This is really quite simple; if ?delete is on the URL, delete the installer...
	if (isset($_GET['delete']))
	{
		if (isset($_SESSION['installer_temp_ftp']))
		{
			$ftp = new ftp_connection($_SESSION['installer_temp_ftp']['server'], $_SESSION['installer_temp_ftp']['port'], $_SESSION['installer_temp_ftp']['username'], $_SESSION['installer_temp_ftp']['password']);
			$ftp->chdir($_SESSION['installer_temp_ftp']['path']);

			$ftp->unlink('install.php');
			$ftp->unlink('webinstall.sql');
			$ftp->unlink('install_1-1.sql');

			$ftp->close();

			unset($_SESSION['installer_temp_ftp']);
		}
		else
		{
			@unlink(__FILE__);
			@unlink(dirname(__FILE__) . '/webinstall.sql');
			@unlink(dirname(__FILE__) . '/install_1-1.sql');
		}

		// Now just redirect to a blank.gif...
		header('Location: http://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']) . dirname($_SERVER['PHP_SELF']) . '/Themes/default/images/blank.gif');
		exit;
	}

	// Force an integer step, defaulting to 0.
	$_GET['step'] = (int) @$_GET['step'];
}

// Load the list of language files, and the current language file.
function load_lang_file()
{
	global $txt;

	$GLOBALS['detected_languages'] = array();

	// Make sure the languages directory actually exists.
	if (file_exists(dirname(__FILE__) . '/Themes/default/languages'))
	{
		// Find all the "Install" language files in the directory.
		$dir = dir(dirname(__FILE__) . '/Themes/default/languages');
		while ($entry = $dir->read())
		{
			if (substr($entry, 0, 8) == 'Install.' && substr($entry, -4) == '.php')
				$GLOBALS['detected_languages'][$entry] = ucfirst(substr($entry, 8, strlen($entry) - 12));
		}
		$dir->close();
	}

	// Didn't find any, show an error message!
	if (empty($GLOBALS['detected_languages']))
	{
		// Let's not cache this message, eh?
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache');

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>SMF Installer: Error!</title>
	</head>
	<body style="font-family: sans-serif;"><div style="width: 600px;">
		<h1 style="font-size: 14pt;">A critical error has occurred.</h1>

		<p>This installer was unable to find the installer\'s language file or files.  They should be found under:</p>

		<div style="margin: 1ex; font-family: monospace; font-weight: bold;">', dirname($_SERVER['PHP_SELF']) != '/' ? dirname($_SERVER['PHP_SELF']) : '', '/Themes/default/languages</div>

		<p>In some cases, FTP clients do not properly upload files with this many folders.  Please double check to make sure you <span style="font-weight: 600;">have uploaded all the files in the distribution</span>.</p>
		<p>If that doesn\'t help, please make sure this install.php file is in the same place as the Themes folder.</p>

		<p>If you continue to get this error message, feel free to <a href="http://support.simplemachines.org/">look to us for support</a>.</p>
	</div></body>
</html>';
		die;
	}

	// Override the language file?
	if (isset($_GET['lang_file']))
		$_SESSION['installer_temp_lang'] = $_GET['lang_file'];
	elseif (isset($GLOBALS['HTTP_GET_VARS']['lang_file']))
		$_SESSION['installer_temp_lang'] = $GLOBALS['HTTP_GET_VARS']['lang_file'];

	// Make sure it exists, if it doesn't reset it.
	if (!isset($_SESSION['installer_temp_lang']) || !file_exists(dirname(__FILE__) . '/Themes/default/languages/' . $_SESSION['installer_temp_lang']))
	{
		// Use the first one...
		list ($_SESSION['installer_temp_lang']) = array_keys($GLOBALS['detected_languages']);

		// If we have english and some other language, use the other language.  We Americans hate english :P.
		if ($_SESSION['installer_temp_lang'] == 'Install.english.php' && count($GLOBALS['detected_languages']) > 1)
			list (, $_SESSION['installer_temp_lang']) = array_keys($GLOBALS['detected_languages']);
	}

	// And now include the actual language file itself.
	require_once(dirname(__FILE__) . '/Themes/default/languages/' . $_SESSION['installer_temp_lang']);
}

// Step zero: Finding out how and where to install.
function doStep0()
{
	global $txt;

	// Just so people using older versions of PHP aren't left in the cold.
	if (!isset($_SERVER['PHP_SELF']))
		$_SERVER['PHP_SELF'] = isset($GLOBALS['HTTP_SERVER_VARS']['PHP_SELF']) ? $GLOBALS['HTTP_SERVER_VARS']['PHP_SELF'] : 'install.php';

	// Show a language selection...
	if (count($GLOBALS['detected_languages']) > 1)
	{
		echo '
				<div style="padding-bottom: 2ex; text-align: ', empty($txt['lang_rtl']) ? 'right' : 'left', ';">
					<form action="', $_SERVER['PHP_SELF'], '" method="get">
						<label for="installer_language">', $txt['installer_language'], ':</label> <select id="installer_language" name="lang_file" onchange="location.href = \'', $_SERVER['PHP_SELF'], '?lang_file=\' + this.options[this.selectedIndex].value;">';

		foreach ($GLOBALS['detected_languages'] as $lang => $name)
			echo '
							<option', isset($_SESSION['installer_temp_lang']) && $_SESSION['installer_temp_lang'] == $lang ? ' selected="selected"' : '', ' value="', $lang, '">', $name, '</option>';

		echo '
						</select>

						<noscript><input type="submit" value="', $txt['installer_language_set'], '" /></noscript>
					</form>
				</div>';
	}

	// Check the PHP version.
	if ((!function_exists('version_compare') || version_compare($GLOBALS['required_php_version'], PHP_VERSION) > 0) && !isset($_GET['overphp']))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_php_too_low'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_bad_try_again'], '
				</div>';

		return false;
	}

	// Is MySQL even compiled in?
	if (!function_exists('mysql_connect'))
		$error = 'error_mysql_missing';
	// How about session support?  Some crazy sysadmin remove it?
	elseif (!function_exists('session_start'))
		$error = 'error_session_missing';
	// Make sure they uploaded all the files.
	elseif (!file_exists(dirname(__FILE__) . '/index.php') || !file_exists(dirname(__FILE__) . '/install_1-1.sql'))
		$error = 'error_missing_files';
	// Very simple check on the session.save_path for Windows.
	// !!! Move this down later if they don't use database-driven sessions?
	elseif (session_save_path() == '/tmp' && substr(__FILE__, 1, 2) == ':\\')
		$error = 'error_session_save_path';

	// Since each of the three messages would look the same, anyway...
	if (isset($error))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt[$error], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	// Make sure all the files are properly writable (has its own messages...)
	if (!make_files_writable())
		return false;

	// Mod_security blocks everything that smells funny. Let SMF handle security.
	if (!fixModSecurity() && !isset($_GET['overmodsecurity']))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_mod_security'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true&amp;overmodsecurity=true">', $txt['error_message_click'], '</a> ', $txt['error_message_bad_try_again'], '
				</div>';

		return false;
	}


	// Set up the defaults.
	$db_server = @ini_get('mysql.default_host') or $db_server = 'localhost';
	$db_user = isset($_POST['ftp_username']) ? $_POST['ftp_username'] : @ini_get('mysql.default_user');
	$db_name = isset($_POST['ftp_username']) ? $_POST['ftp_username'] : @ini_get('mysql.default_user');
	$db_passwd = @ini_get('mysql.default_password');

	// This is just because it makes it easier for people on Lycos/Tripod UK :P.
	if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] == 'members.lycos.co.uk' && defined('LOGIN'))
	{
		$db_user = LOGIN;
		$db_name = LOGIN . '_uk_db';
	}

	// Should we use a non standard port?
	$db_port = @ini_get('mysql.default_port');
	if (!empty($db_port))
		$db_server .= ':' . $db_port;

	// What host and port are we on?
	$host = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST'];

	// Now, to put what we've learned together... and add a path.
	$url = 'http://' . $host . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));

	// Check if the database sessions will even work.
	$test_dbsession = @ini_get('session.auto_start') != 1 && @version_compare(PHP_VERSION, '4.2.0') != -1;

	echo '
				<div class="panel">
					<form action="' . $_SERVER['PHP_SELF'] . '?step=1" method="post">
						<h2>', $txt['install_settings'], '</h2>
						<h3>', $txt['install_settings_info'], '</h3>

						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="mbname_input">', $txt['install_settings_name'], ':</label></td>
								<td>
									<input type="text" name="mbname" id="mbname_input" value="', $txt['install_settings_name_default'], '" size="65" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['install_settings_name_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="boardurl_input">', $txt['install_settings_url'], ':</label></td>
								<td>
									<input type="text" name="boardurl" id="boardurl_input" value="', $url, '" size="65" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['install_settings_url_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox">', $txt['install_settings_compress'], ':</td>
								<td>
									<input type="checkbox" name="compress" id="compress_check" checked="checked" /> <label for="compress_check">', $txt['install_settings_compress_title'], '</label><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['install_settings_compress_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox">', $txt['install_settings_dbsession'], ':</td>
								<td>
									<input type="checkbox" name="dbsession" id="dbsession_check" checked="checked" /> <label for="dbsession_check">', $txt['install_settings_dbsession_title'], '</label><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $test_dbsession ? $txt['install_settings_dbsession_info1'] : $txt['install_settings_dbsession_info2'], '</div>
								</td>
							</tr>';
	if (strpos(strtolower(PHP_OS), 'win') === false || @version_compare(PHP_VERSION, '4.2.3') != -1)
		echo '
							<tr>
								<td valign="top" class="textbox">', $txt['install_settings_utf8'], ':</td>
								<td>
									<input type="checkbox" name="utf8" id="utf8_check" /> <label for="utf8_check">', $txt['install_settings_utf8_title'], '</label><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['install_settings_utf8_info'], '</div>
								</td>
							</tr>';

	echo '
							<tr>
								<td valign="top" class="textbox">', $txt['install_settings_stats'], ':</td>
								<td>
									<input type="checkbox" name="stats" id="stats_check" /> <label for="stats_check">', $txt['install_settings_stats_title'], '</label><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['install_settings_stats_info'], '</div>
								</td>
							</tr>
						</table>

						<h2>', $txt['mysql_settings'], '</h2>
						<h3>', $txt['mysql_settings_info'], '</h3>

						<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="20%" valign="top" class="textbox"><label for="db_server_input">', $txt['mysql_settings_server'], ':</label></td>
								<td>
									<input type="text" name="db_server" id="db_server_input" value="', $db_server, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['mysql_settings_server_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_user_input">', $txt['mysql_settings_username'], ':</label></td>
								<td>
									<input type="text" name="db_user" id="db_user_input" value="', $db_user, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['mysql_settings_username_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_passwd_input">', $txt['mysql_settings_password'], ':</label></td>
								<td>
									<input type="password" name="db_passwd" id="db_passwd_input" value="', $db_passwd, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['mysql_settings_password_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_name_input">', $txt['mysql_settings_database'], ':</label></td>
								<td>
									<input type="text" name="db_name" id="db_name_input" value="', empty($db_name) ? 'smf' : $db_name, '" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['mysql_settings_database_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="db_prefix_input">', $txt['mysql_settings_prefix'], ':</label></td>
								<td>
									<input type="text" name="db_prefix" id="db_prefix_input" value="smf_" size="30" /><br />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['mysql_settings_prefix_info'], '</div>
								</td>
							</tr>
						</table>

						<div style="margin: 1ex; text-align: ', empty($txt['lang_rtl']) ? 'right' : 'left', ';"><input type="submit" value="', $txt['install_settings_proceed'], '" /></div>
					</form>
				</div>';

	return true;
}

// Step one: Do the SQL thang.
function doStep1()
{
	global $txt, $db_connection;

	if (substr($_POST['boardurl'], -10) == '/index.php')
		$_POST['boardurl'] = substr($_POST['boardurl'], 0, -10);
	elseif (substr($_POST['boardurl'], -1) == '/')
		$_POST['boardurl'] = substr($_POST['boardurl'], 0, -1);
	if (substr($_POST['boardurl'], 0, 7) != 'http://' && substr($_POST['boardurl'], 0, 7) != 'file://' && substr($_POST['boardurl'], 0, 8) != 'https://')
		$_POST['boardurl'] = 'http://' . $_POST['boardurl'];

	// Take care of these variables...
	$vars = array(
		'boardurl' => $_POST['boardurl'],
		'boarddir' => addslashes(dirname(__FILE__)),
		'sourcedir' => addslashes(dirname(__FILE__)) . '/Sources',
		'db_name' => $_POST['db_name'],
		'db_user' => $_POST['db_user'],
		'db_passwd' => isset($_POST['db_passwd']) ? $_POST['db_passwd'] : '',
		'db_server' => $_POST['db_server'],
		'db_prefix' => preg_replace('~[^A-Za-z0-9_$]~', '', $_POST['db_prefix']),
		'mbname' => $_POST['mbname'],
		'language' => substr($_SESSION['installer_temp_lang'], 8, -4),
		// The cookiename is special; we want it to be the same if it ever needs to be reinstalled with the same info.
		'cookiename' => 'SMFCookie' . abs(crc32($_POST['db_name'] . preg_replace('~[^A-Za-z0-9_$]~', '', $_POST['db_prefix'])) % 1000),
	);

	// !!! if (is_numeric(substr($vars['db_prefix'], 0, 1)))

	if (!updateSettingsFile($vars) && substr(__FILE__, 1, 2) == ':\\')
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_windows_chmod'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	// Make sure it works.
	require(dirname(__FILE__) . '/Settings.php');

	// Attempt a connection.
	$db_connection = @mysql_connect($db_server, $db_user, $db_passwd);

	// No dice?  Let's try adding the prefix they specified, just in case they misread the instructions ;).
	if (!$db_connection)
	{
		$mysql_error = mysql_error();

		$db_connection = @mysql_connect($db_server, $_POST['db_prefix'] . $db_user, $db_passwd);
		if ($db_connection != false)
		{
			$db_user = $_POST['db_prefix'] . $db_user;
			updateSettingsFile(array('db_user' => $db_user));
		}
	}

	// Still no connection?  Big fat error message :P.
	if (!$db_connection)
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_mysql_connect'], '</div>

					<div style="margin: 2.5ex; font-family: monospace;"><b>', $mysql_error, '</b></div>

					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';
		return false;
	}

	// Do they meet the install requirements?
	// !!! Old client, new server?
	if (version_compare($GLOBALS['required_mysql_version'], preg_replace('~\-.+?$~', '', min(mysql_get_server_info(), mysql_get_client_info()))) > 0)
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_mysql_too_low'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	// Let's try that database on for size...
	if ($db_name != '')
		mysql_query("
			CREATE DATABASE IF NOT EXISTS `$db_name`", $db_connection);

	// Okay, let's try the prefix if it didn't work...
	if (!mysql_select_db($db_name, $db_connection) && $db_name != '')
	{
		mysql_query("
			CREATE DATABASE IF NOT EXISTS `$_POST[db_prefix]$db_name`", $db_connection);

		if (mysql_select_db($_POST['db_prefix'] . $db_name, $db_connection))
		{
			$db_name = $_POST['db_prefix'] . $db_name;
			updateSettingsFile(array('db_name' => $db_name));
		}
	}

	// Okay, now let's try to connect...
	if (!mysql_select_db($db_name, $db_connection))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', sprintf($txt['error_mysql_database'], $db_name), '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	// Before running any of the queries, let's make sure another version isn't already installed.
	$result = mysql_query("
		SELECT value
		FROM {$db_prefix}settings
		WHERE variable = 'smfVersion'
		LIMIT 1");
	if ($result !== false)
	{
		list ($database_version) = mysql_fetch_row($result);
		mysql_free_result($result);

		// Do they match?  If so, this is just a refresh so charge on!
		if ($database_version != $GLOBALS['current_smf_version'])
		{
			echo '
					<div class="error_message">
						<div style="color: red;">', $txt['error_versions_do_not_match'], '</div>
						<br />
						<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
					</div>';

			return false;
		}
	}

	// UTF-8 requires a setting to override the language charset.
	if (isset($_POST['utf8']))
	{
		if (version_compare('4.1.0', preg_replace('~\-.+?$~', '', mysql_get_server_info())) > 0)
		{
			echo '
					<div class="error_message">
						<div style="color: red;">', $txt['error_utf8_mysql_version'], '</div>
						<br />
						<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
					</div>';

			return false;
		}
		else
		{
			updateSettingsFile(array('db_character_set' => 'utf8'));
			mysql_query("
				SET NAMES utf8");
		}
	}

	$replaces = array(
		'{$db_prefix}' => $db_prefix,
		'{$boarddir}' => addslashes(dirname(__FILE__)),
		'{$boardurl}' => $_POST['boardurl'],
		'{$enableCompressedOutput}' => isset($_POST['compress']) ? '1' : '0',
		'{$databaseSession_enable}' => isset($_POST['dbsession']) ? '1' : '0',
		'{$smf_version}' => $GLOBALS['current_smf_version'],
	);
	foreach ($txt as $key => $value)
	{
		if (substr($key, 0, 8) == 'default_')
			$replaces['{$' . $key . '}'] = addslashes($value);
	}
	$replaces['{$default_reserved_names}'] = strtr($replaces['{$default_reserved_names}'], array('\\\\n' => '\\n'));

	// If the UTF-8 setting was enabled, add it to the table definitions.
	if (isset($_POST['utf8']))
		$replaces[') TYPE=MyISAM;'] = ') TYPE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;';

	// Read in the SQL.  Turn this on and that off... internationalize... etc.
	$sql_lines = explode("\n", strtr(implode(' ', file(dirname(__FILE__) . '/install_1-1.sql')), $replaces));

	// Execute the SQL.
	$current_statement = '';
	$failures = array();
	$exists = array();
	foreach ($sql_lines as $count => $line)
	{
		// No comments allowed!
		if (substr(trim($line), 0, 1) != '#')
			$current_statement .= "\n" . rtrim($line);

		// Is this the end of the query string?
		if (empty($current_statement) || (preg_match('~;[\s]*$~s', $line) == 0 && $count != count($sql_lines)))
			continue;

		// Does this table already exist?  If so, don't insert more data into it!
		if (preg_match('~^\s*INSERT INTO ([^\s\n\r]+?)~', $current_statement, $match) != 0 && in_array($match[1], $exists))
		{
			$current_statement = '';
			continue;
		}

		if (mysql_query($current_statement) === false)
		{
			// Error 1050: Table already exists!
			if (mysql_errno($db_connection) === 1050 && preg_match('~^\s*CREATE TABLE ([^\s\n\r]+?)~', $current_statement, $match) == 1)
				$exists[] = $match[1];
			else
				$failures[$count] = mysql_error();
		}

		$current_statement = '';
	}

	// Make sure UTF will be used globally.
	if (isset($_POST['utf8']))
		mysql_query("
			INSERT INTO {$db_prefix}settings
				(variable, value)
			VALUES ('global_character_set', 'UTF-8')");

	// Maybe we can auto-detect better cookie settings?
	preg_match('~^http[s]?://([^\.]+?)([^/]*?)(/.*)?$~', $_POST['boardurl'], $matches);
	if (!empty($matches))
	{
		// Default = both off.
		$localCookies = false;
		$globalCookies = false;

		// Okay... let's see.  Using a subdomain other than www.? (not a perfect check.)
		if ($matches[2] != '' && (strpos(substr($matches[2], 1), '.') === false || in_array($matches[1], array('forum', 'board', 'community', 'forums', 'support', 'chat', 'help', 'talk', 'boards', 'www'))))
			$globalCookies = true;
		// If there's a / in the middle of the path, or it starts with ~... we want local.
		if (isset($matches[3]) && strlen($matches[3]) > 3 && (substr($matches[3], 0, 2) == '/~' || strpos(substr($matches[3], 1), '/') !== false))
			$localCookies = true;

		$rows = array();
		if ($globalCookies)
			$rows[] = "('globalCookies', '1')";
		if ($localCookies)
			$rows[] = "('localCookies', '1')";

		if (!empty($rows))
			mysql_query("
				INSERT INTO {$db_prefix}settings
					(variable, value)
				VALUES " . implode(',
					', $rows));
	}

	// Are we allowing stat collection?
	if (isset($_POST['stats']) && substr($_POST['boardurl'], 0, 16) != 'http://localhost')
	{
		// Attempt to register the site etc.
		$fp = @fsockopen("www.simplemachines.org", 80, $errno, $errstr);
		if ($fp)
		{
			$out = "GET /smf/stats/register_stats.php?site=" . base64_encode($_POST['boardurl']) . " HTTP/1.1\r\n";
			$out .= "Host: www.simplemachines.org\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);

			$return_data = '';
			while (!feof($fp))
				$return_data .= fgets($fp, 128);

			fclose($fp);

			// Get the unique site ID.
			preg_match('~SITE-ID:\s(\w{10})~', $return_data, $ID);

			if (!empty($ID[1]))
				mysql_query("
					INSERT INTO {$db_prefix}settings
						(variable, value)
					VALUES
						('allow_sm_stats', '$ID[1]')");
		}
	}

	// Let's optimize those new tables.
	$tables = mysql_list_tables($db_name);
	$table_names = array();
	while ($table = mysql_fetch_row($tables))
		$table_names[] = $table[0];
	mysql_free_result($tables);

	mysql_query('
		OPTIMIZE TABLE `' . implode('`, `', $table_names) . '`') or $db_messed = true;
	if (!empty($db_messed))
		$failures[-1] = mysql_error($db_connection);

	if (!empty($failures))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_mysql_queries'], '</div>
					<div style="margin: 2.5ex;">';

		foreach ($failures as $line => $fail)
			echo '
						<b>', $txt['error_mysql_queries_line'], $line + 1, ':</b> ', nl2br(htmlspecialchars($fail)), '<br />';

		echo '
					</div>
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	// Check for the ALTER privilege.
	if (mysql_query("ALTER TABLE {$db_prefix}boards ORDER BY ID_BOARD") === false)
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_mysql_alter_priv'], '</div>
					<br />
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}

	if (!empty($exists))
		echo '
				<div class="panel">
					<h2>', $txt['user_refresh_install'], '</h2>
					', $txt['user_refresh_install_desc'], '
				</div>';

	return doStep2a();
}

// Step two-A: Ask for the administrator login information.
function doStep2a()
{
	global $txt;

	if (!isset($_POST['username']))
		$_POST['username'] = '';
	if (!isset($_POST['email']))
		$_POST['email'] = '';

	echo '
				<div class="panel">
					<form action="' . $_SERVER['PHP_SELF'] . '?step=2" method="post" onsubmit="if (this.password1.value == this.password2.value) return true; else {alert(\'', $txt['error_user_settings_again_match'], '\'); return false;}">
						<h2>', $txt['user_settings'], '</h2>
						<h3>', $txt['user_settings_info'], '</h3>

						<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 2ex;">
							<tr>
								<td width="18%" valign="top" class="textbox"><label for="username">', $txt['user_settings_username'], ':</label></td>
								<td>
									<input type="text" name="username" id="username" value="', $_POST['username'], '" size="40" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['user_settings_username_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="password1">', $txt['user_settings_password'], ':</label></td>
								<td>
									<input type="password" name="password1" id="password1" size="40" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['user_settings_password_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="password2">', $txt['user_settings_again'], ':</label></td>
								<td>
									<input type="password" name="password2" id="password2" size="40" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['user_settings_again_info'], '</div>
								</td>
							</tr><tr>
								<td valign="top" class="textbox"><label for="email">', $txt['user_settings_email'], ':</label></td>
								<td>
									<input type="text" name="email" id="email" value="', $_POST['email'], '" size="40" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['user_settings_email_info'], '</div>
								</td>
							</tr>
						</table>

						<h2>', $txt['user_settings_database'], '</h2>
						<h3>', $txt['user_settings_database_info'], '</h3>

						<div style="margin-bottom: 2ex; padding-', empty($txt['lang_rtl']) ? 'left' : 'right', ': 17%;">
							<input type="password" name="password3" size="30" />
						</div>

						<div style="margin: 1ex; text-align: ', empty($txt['lang_rtl']) ? 'right' : 'left', ';"><input type="submit" value="', $txt['user_settings_proceed'], '" /></div>
					</form>
				</div>';

	return true;
}

// Step two: Create the administrator, and finish.
function doStep2()
{
	global $txt, $db_prefix, $db_connection, $HTTP_SESSION_VARS, $cookiename;
	global $func, $db_character_set, $mbname, $context, $scripturl, $boardurl;
	global $current_smf_version;

	// Load the SQL server login information.
	require_once(dirname(__FILE__) . '/Settings.php');

	if (!isset($_POST['password3']))
		return doStep2a();

	$db_connection = @mysql_connect($db_server, $db_user, $_POST['password3']);
	if (!$db_connection)
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_mysql_connect'], '</div>
				</div>';

		return doStep2a();
	}
	if (!mysql_select_db($db_name, $db_connection))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', sprintf($txt['error_mysql_database'], $db_name), '</div>
				</div>
				<br />';

		return doStep2a();
	}

	// Let them try again...
	if ($_POST['password1'] != $_POST['password2'])
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_user_settings_again_match'], '</div>
				</div>
				<br />';

		return doStep2a();
	}

	if (!file_exists($sourcedir . '/Subs.php'))
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_subs_missing'], '</div>
				</div>
				<br />';

		return doStep2a();
	}

	updateSettingsFile(array('webmaster_email' => $_POST['email']));

	chdir(dirname(__FILE__));

	define('SMF', 1);
	require_once($sourcedir . '/Subs.php');
	require_once($sourcedir . '/Load.php');
	require_once($sourcedir . '/Security.php');
	require_once($sourcedir . '/Subs-Auth.php');

	// Define the sha1 function, if it doesn't exist.
	if (!function_exists('sha1'))
		require_once($sourcedir . '/Subs-Compat.php');

	if (isset($db_character_set))
		mysql_query("
			SET NAMES $db_character_set");

	$result = mysql_query("
		SELECT ID_MEMBER, passwordSalt
		FROM {$db_prefix}members
		WHERE memberName = '$_POST[username]' OR emailAddress = '$_POST[email]'
		LIMIT 1");
	if (mysql_num_rows($result) != 0)
	{
		list ($id, $salt) = mysql_fetch_row($result);
		mysql_free_result($result);

		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_user_settings_taken'], '</div>
				</div>
				<br />';
	}
	elseif (preg_match('~[<>&"\'=\\\]~', $_POST['username']) != 0 || strlen($_POST['username']) > 25 || $_POST['username'] == '_' || $_POST['username'] == '|' || strpos($_POST['username'], '[code') !== false || strpos($_POST['username'], '[/code') !== false)
	{
		// Initialize some variables needed for the language file.
		$context = array(
			'forum_name' => $mbname,
		);
		$modSettings = array(
			'lastActive' => '15',
			'hotTopicPosts' => '15',
			'hotTopicVeryPosts' => '25',
			'smfVersion' => $current_smf_version,
		);
		$scripturl = $boardurl . '/index.php';

		require_once(dirname(__FILE__) . '/Themes/default/languages/' . strtr($_SESSION['installer_temp_lang'], array('Install' => 'index')));
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt[240], '</div>
				</div>
				<br />';

		// Try the previous step again.
		return doStep2a();
	}
	elseif (empty($_POST['email']) || preg_match('~^[0-9A-Za-z=_+\-/][0-9A-Za-z=_\'+\-/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$~', stripslashes($_POST['email'])) === 0 || strlen(stripslashes($_POST['email'])) > 255)
	{
		// Artificially fill some of the globals needed for the language files.
		$context = array(
			'forum_name' => $mbname,
		);
		$modSettings = array(
			'lastActive' => '15',
			'hotTopicPosts' => '15',
			'hotTopicVeryPosts' => '25',
			'smfVersion' => $current_smf_version,
		);
		$scripturl = $boardurl . '/index.php';

		require_once(dirname(__FILE__) . '/Themes/default/languages/' . strtr($_SESSION['installer_temp_lang'], array('Install' => 'index')));
		require_once(dirname(__FILE__) . '/Themes/default/languages/' . strtr($_SESSION['installer_temp_lang'], array('Install' => 'Login')));
		echo '
				<div class="error_message">
					<div style="color: red;">', sprintf($txt[500], $_POST['username']), '</div>
				</div>
				<br />';

		// One step back, this time fill out a proper email address.
		return doStep2a();
	}
	elseif ($_POST['username'] != '')
	{
		$salt = substr(md5(rand()), 0, 4);

		// Format the username properly.
		$_POST['username'] = preg_replace('~[\t\n\r\x0B\0\xA0]+~', ' ', $_POST['username']);
		$ip = isset($_SERVER['REMOTE_ADDR']) ? addslashes(substr(stripslashes($_SERVER['REMOTE_ADDR']), 0, 255)) : '';

		$request = mysql_query("
			INSERT INTO {$db_prefix}members
				(memberName, realName, passwd, emailAddress, ID_GROUP, posts, dateRegistered, hideEmail, passwordSalt, lngfile, personalText, avatar, memberIP, memberIP2, buddy_list, pm_ignore_list, messageLabels, websiteTitle, websiteUrl, location, ICQ, MSN, signature, usertitle, secretQuestion, additionalGroups)
			VALUES (SUBSTRING('$_POST[username]', 1, 25), SUBSTRING('$_POST[username]', 1, 25), '" . sha1(strtolower($_POST['username']) . $_POST['password1']) . "', '$_POST[email]', 1, '0', '" . time() . "', '0', '$salt', '', '', '', '$ip', '$ip', '', '', '', '', '', '', '', '', '', '', '', '')");

		// Awww, crud!
		if ($request === false)
		{
			echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_user_settings_query'], '</div>

					<div style="margin: 2ex;">', nl2br(htmlspecialchars(mysql_error($db_connection))), '</div>

					<a href="', $_SERVER['PHP_SELF'], '?step=2">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

			return false;
		}

		$id = mysql_insert_id();
	}

	// Automatically log them in ;).
	if (isset($id) && isset($salt))
		setLoginCookie(3153600 * 60, $id, sha1(sha1(strtolower($_POST['username']) . $_POST['password1']) . $salt));

	$result = mysql_query("
		SELECT value
		FROM {$db_prefix}settings
		WHERE variable = 'databaseSession_enable'");
	if (mysql_num_rows($result) != 0)
		list ($db_sessions) = mysql_fetch_row($result);
	mysql_free_result($result);

	if (empty($db_sessions))
	{
		if (@version_compare(PHP_VERSION, '4.2.0') == -1)
			$HTTP_SESSION_VARS['php_412_bugfix'] = true;
		$_SESSION['admin_time'] = time();
	}
	else
	{
		$_SERVER['HTTP_USER_AGENT'] = addslashes(substr($_SERVER['HTTP_USER_AGENT'], 0, 211));

		mysql_query("
			INSERT INTO {$db_prefix}sessions
				(session_id, last_update, data)
			VALUES ('" . session_id() . "', " . time() . ",
				'USER_AGENT|s:" . strlen(stripslashes($_SERVER['HTTP_USER_AGENT'])) . ":\"$_SERVER[HTTP_USER_AGENT]\";admin_time|i:" . time() . ";')");
	}
	updateStats('member');
	updateStats('message');
	updateStats('topic');

	// This function is needed to do the updateStats('subject') call.
	$func['strtolower'] = $db_character_set === 'utf8' || $txt['lang_character_set'] === 'UTF-8' ? create_function('$string', '
		return $string;') : 'strtolower';

	$request = mysql_query("
		SELECT ID_MSG
		FROM {$db_prefix}messages
		WHERE ID_MSG = 1
			AND modifiedTime = 0
		LIMIT 1");
	if (mysql_num_rows($request) > 0)
		updateStats('subject', 1, addslashes(htmlspecialchars($txt['default_topic_subject'])));
	mysql_free_result($request);

	echo '
				<div class="panel">
					<h2>', $txt['congratulations'], '</h2>
					<br />
					', $txt['congratulations_help'], '<br />
					<br />';

	if (is_writable(dirname(__FILE__)) && substr(__FILE__, 1, 2) != ':\\')
		echo '
					<i>', $txt['still_writable'], '</i><br />
					<br />';

	// Don't show the box if it's like 99% sure it won't work :P.
	if (isset($_SESSION['installer_temp_ftp']) || is_writable(dirname(__FILE__)) || is_writable(__FILE__))
		echo '
					<div style="margin: 1ex; font-weight: bold;">
						<label for="delete_self"><input type="checkbox" id="delete_self" onclick="doTheDelete();" /> ', $txt['delete_installer'], !isset($_SESSION['installer_temp_ftp']) ? ' ' . $txt['delete_installer_maybe'] : '', '</label>
					</div>
					<script language="JavaScript" type="text/javascript"><!-- // --><![CDATA[
						function doTheDelete()
						{
							var theCheck = document.getElementById ? document.getElementById("delete_self") : document.all.delete_self;
							var tempImage = new Image();

							tempImage.src = "', $_SERVER['PHP_SELF'], '?delete=1&" + (new Date().getTime());
							tempImage.width = 0;
							theCheck.disabled = true;
						}
					// ]]></script>
					<br />';

	echo '
					', sprintf($txt['go_to_your_forum'], $boardurl . '/index.php'), '<br />
					<br />
					', $txt['good_luck'], '
				</div>';

	return true;
}

// http://www.faqs.org/rfcs/rfc959.html
class ftp_connection
{
	var $connection = 'no_connection', $error = false, $last_message, $pasv = array();

	// Create a new FTP connection...
	function ftp_connection($ftp_server, $ftp_port = 21, $ftp_user = 'anonymous', $ftp_pass = 'ftpclient@simplemachines.org')
	{
		if ($ftp_server !== null)
			$this->connect($ftp_server, $ftp_port, $ftp_user, $ftp_pass);
	}

	function connect($ftp_server, $ftp_port = 21, $ftp_user = 'anonymous', $ftp_pass = 'ftpclient@simplemachines.org')
	{
		if (substr($ftp_server, 0, 6) == 'ftp://')
			$ftp_server = substr($ftp_server, 6);
		elseif (substr($ftp_server, 0, 7) == 'ftps://')
			$ftp_server = 'ssl://' . substr($ftp_server, 7);
		if (substr($ftp_server, 0, 7) == 'http://')
			$ftp_server = substr($ftp_server, 7);
		$ftp_server = strtr($ftp_server, array('/' => '', ':' => '', '@' => ''));

		// Connect to the FTP server.
		$this->connection = @fsockopen($ftp_server, $ftp_port, $err, $err, 5);
		if (!$this->connection)
		{
			$this->error = 'bad_server';
			return;
		}

		// Get the welcome message...
		if (!$this->check_response(220))
		{
			$this->error = 'bad_response';
			return;
		}

		// Send the username, it should ask for a password.
		fwrite($this->connection, 'USER ' . $ftp_user . "\r\n");
		if (!$this->check_response(331))
		{
			$this->error = 'bad_username';
			return;
		}

		// Now send the password... and hope it goes okay.
		fwrite($this->connection, 'PASS ' . $ftp_pass . "\r\n");
		if (!$this->check_response(230))
		{
			$this->error = 'bad_password';
			return;
		}
	}

	function chdir($ftp_path)
	{
		if (!is_resource($this->connection))
			return false;

		// No slash on the end, please...
		if (substr($ftp_path, -1) == '/')
			$ftp_path = substr($ftp_path, 0, -1);

		fwrite($this->connection, 'CWD ' . $ftp_path . "\r\n");
		if (!$this->check_response(250))
		{
			$this->error = 'bad_path';
			return false;
		}

		return true;
	}

	function chmod($ftp_file, $chmod)
	{
		if (!is_resource($this->connection))
			return false;

		// Convert the chmod value from octal (0777) to text ("777").
		fwrite($this->connection, 'SITE CHMOD ' . decoct($chmod) . ' ' . $ftp_file . "\r\n");
		if (!$this->check_response(200))
		{
			$this->error = 'bad_file';
			return false;
		}

		return true;
	}

	function unlink($ftp_file)
	{
		// We are actually connected, right?
		if (!is_resource($this->connection))
			return false;

		// Delete file X.
		fwrite($this->connection, 'DELE ' . $ftp_file . "\r\n");
		if (!$this->check_response(250))
		{
			fwrite($this->connection, 'RMD ' . $ftp_file . "\r\n");

			// Still no love?
			if (!$this->check_response(250))
			{
				$this->error = 'bad_file';
				return false;
			}
		}

		return true;
	}

	function check_response($desired)
	{
		// Wait for a response that isn't continued with -, but don't wait too long.
		$time = time();
		do
			$this->last_message = fgets($this->connection, 1024);
		while (substr($this->last_message, 3, 1) != ' ' && time() - $time < 5);

		// Was the desired response returned?
		return is_array($desired) ? in_array(substr($this->last_message, 0, 3), $desired) : substr($this->last_message, 0, 3) == $desired;
	}

	function passive()
	{
		// We can't create a passive data connection without a primary one first being there.
		if (!is_resource($this->connection))
			return false;

		// Request a passive connection - this means, we'll talk to you, you don't talk to us.
		@fwrite($this->connection, "PASV\r\n");
		$time = time();
		do
			$response = fgets($this->connection, 1024);
		while (substr($response, 3, 1) != ' ' && time() - $time < 5);

		// If it's not 227, we weren't given an IP and port, which means it failed.
		if (substr($response, 0, 4) != '227 ')
		{
			$this->error = 'bad_response';
			return false;
		}

		// Snatch the IP and port information, or die horribly trying...
		if (preg_match('~\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))\)~', $response, $match) == 0)
		{
			$this->error = 'bad_response';
			return false;
		}

		// This is pretty simple - store it for later use ;).
		$this->pasv = array('ip' => $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4], 'port' => $match[5] * 256 + $match[6]);

		return true;
	}

	function create_file($ftp_file)
	{
		// First, we have to be connected... very important.
		if (!is_resource($this->connection))
			return false;

		// I'd like one passive mode, please!
		if (!$this->passive())
			return false;

		// Seems logical enough, so far...
		fwrite($this->connection, 'STOR ' . $ftp_file . "\r\n");

		// Okay, now we connect to the data port.  If it doesn't work out, it's probably "file already exists", etc.
		$fp = @fsockopen($this->pasv['ip'], $this->pasv['port'], $err, $err, 5);
		if (!$fp || !$this->check_response(150))
		{
			$this->error = 'bad_file';
			@fclose($fp);
			return false;
		}

		// This may look strange, but we're just closing it to indicate a zero-byte upload.
		fclose($fp);
		if (!$this->check_response(226))
		{
			$this->error = 'bad_response';
			return false;
		}

		return true;
	}

	function list_dir($ftp_path = '', $search = false)
	{
		// Are we even connected...?
		if (!is_resource($this->connection))
			return false;

		// Passive... non-agressive...
		if (!$this->passive())
			return false;

		// Get the listing!
		fwrite($this->connection, 'LIST -1' . ($search ? 'R' : '') . ($ftp_path == '' ? '' : ' ' . $ftp_path) . "\r\n");

		// Connect, assuming we've got a connection.
		$fp = @fsockopen($this->pasv['ip'], $this->pasv['port'], $err, $err, 5);
		if (!$fp || !$this->check_response(array(150, 125)))
		{
			$this->error = 'bad_response';
			@fclose($fp);
			return false;
		}

		// Read in the file listing.
		$data = '';
		while (!feof($fp))
			$data .= fread($fp, 4096);;
		fclose($fp);

		// Everything go okay?
		if (!$this->check_response(226))
		{
			$this->error = 'bad_response';
			return false;
		}

		return $data;
	}

	function locate($file, $listing = null)
	{
		if ($listing === null)
			$listing = $this->list_dir('', true);
		$listing = explode("\n", $listing);

		@fwrite($this->connection, "PWD\r\n");
		$time = time();
		do
			$response = fgets($this->connection, 1024);
		while (substr($response, 3, 1) != ' ' && time() - $time < 5);

		// Check for 257!
		if (preg_match('~^257 "(.+?)" ~', $response, $match) != 0)
			$current_dir = strtr($match[1], array('""' => '"'));
		else
			$current_dir = '';

		for ($i = 0, $n = count($listing); $i < $n; $i++)
		{
			if (trim($listing[$i]) == '' && isset($listing[$i + 1]))
			{
				$current_dir = substr(trim($listing[++$i]), 0, -1);
				$i++;
			}

			// Okay, this file's name is:
			$listing[$i] = $current_dir . '/' . trim(strlen($listing[$i]) > 30 ? strrchr($listing[$i], ' ') : $listing[$i]);

			if (substr($file, 0, 1) == '*' && substr($listing[$i], -(strlen($file) - 1)) == substr($file, 1))
				return $listing[$i];
			if (substr($file, -1) == '*' && substr($listing[$i], 0, strlen($file) - 1) == substr($file, 0, -1))
				return $listing[$i];
			if (basename($listing[$i]) == $file || $listing[$i] == $file)
				return $listing[$i];
		}

		return false;
	}

	function create_dir($ftp_dir)
	{
		// We must be connected to the server to do something.
		if (!is_resource($this->connection))
			return false;

		// Make this new beautiful directory!
		fwrite($this->connection, 'MKD ' . $ftp_dir . "\r\n");
		if (!$this->check_response(257))
		{
			$this->error = 'bad_file';
			return false;
		}

		return true;
	}

	function detect_path($filesystem_path, $lookup_file = null)
	{
		$username = '';

		if (isset($_SERVER['DOCUMENT_ROOT']))
		{
			if (preg_match('~^/home[2]?/([^/]+?)/public_html~', $_SERVER['DOCUMENT_ROOT'], $match))
			{
				$username = $match[1];

				$path = strtr($_SERVER['DOCUMENT_ROOT'], array('/home/' . $match[1] . '/' => '', '/home2/' . $match[1] . '/' => ''));

				if (substr($path, -1) == '/')
					$path = substr($path, 0, -1);

				if (strlen(dirname($_SERVER['PHP_SELF'])) > 1)
					$path .= dirname($_SERVER['PHP_SELF']);
			}
			elseif (substr($filesystem_path, 0, 9) == '/var/www/')
				$path = substr($filesystem_path, 8);
			else
				$path = strtr(strtr($filesystem_path, array('\\' => '/')), array($_SERVER['DOCUMENT_ROOT'] => ''));
		}
		else
			$path = '';

		if (is_resource($this->connection) && $this->list_dir($path) == '')
		{
			$data = $this->list_dir('', true);

			if ($lookup_file === null)
				$lookup_file = $_SERVER['PHP_SELF'];

			$found_path = dirname($this->locate('*' . basename(dirname($lookup_file)) . '/' . basename($lookup_file), $data));
			if ($found_path == false)
				$found_path = dirname($this->locate(basename($lookup_file)));
			if ($found_path != false)
				$path = $found_path;
		}
		elseif (is_resource($this->connection))
			$found_path = true;

		return array($username, $path, isset($found_path));
	}

	function close()
	{
		// Goodbye!
		fwrite($this->connection, "QUIT\r\n");
		fclose($this->connection);

		return true;
	}
}

function make_files_writable()
{
	global $txt;

	$writable_files = array(
		'attachments',
		'avatars',
		'Packages',
		'Packages/installed.list',
		'Smileys',
		'Themes',
		'agreement.txt',
		'Settings.php',
		'Settings_bak.php'
	);
	$extra_files = array(
		'Themes/classic/index.template.php',
		'Themes/classic/style.css'
	);
	foreach ($GLOBALS['detected_languages'] as $lang => $temp)
		$extra_files[] = 'Themes/default/languages/' . $lang;

	$failure = false;

	// With mod_security installed, we could attempt to fix it with .htaccess.
	if (function_exists('apache_get_modules') && in_array('mod_security', apache_get_modules()))
		$writable_files[] = file_exists(dirname(__FILE__) . '/.htaccess') ? '.htaccess' : '.';

	// On linux, it's easy - just use is_writable!
	if (substr(__FILE__, 1, 2) != ':\\')
	{
		foreach ($writable_files as $file)
		{
			if (!is_writable(dirname(__FILE__) . '/' . $file))
			{
				@chmod(dirname(__FILE__) . '/' . $file, 0755);

				// Well, 755 hopefully worked... if not, try 777.
				$failure |= !is_writable(dirname(__FILE__) . '/' . $file) && !@chmod(dirname(__FILE__) . '/' . $file, 0777);
			}
		}
		foreach ($extra_files as $file)
			@chmod(dirname(__FILE__) . (empty($file) ? '' : '/' . $file), 0777);
	}
	// Windows is trickier.  Let's try opening for r+...
	else
	{
		foreach ($writable_files as $file)
		{
			// Folders can't be opened for write... but the index.php in them can ;).
			if (is_dir(dirname(__FILE__) . '/' . $file))
				$file .= '/index.php';

			// Funny enough, chmod actually does do something on windows - it removes the read only attribute.
			@chmod(dirname(__FILE__) . '/' . $file, 0777);
			$fp = @fopen(dirname(__FILE__) . '/' . $file, 'r+');

			// Hmm, okay, try just for write in that case...
			if (!$fp)
				$fp = @fopen(dirname(__FILE__) . '/' . $file, 'w');

			$failure |= !$fp;
			@fclose($fp);
		}
		foreach ($extra_files as $file)
			@chmod(dirname(__FILE__) . (empty($file) ? '' : '/' . $file), 0777);
	}

	if (!isset($_SERVER))
		return !$failure;

	// It's not going to be possible to use FTP on windows to solve the problem...
	if ($failure && substr(__FILE__, 1, 2) == ':\\')
	{
		echo '
				<div class="error_message">
					<div style="color: red;">', $txt['error_windows_chmod'], '</div>
					<ul style="margin: 2.5ex; font-family: monospace;">
						<li>', implode('</li>
						<li>', $writable_files), '</li>
					</ul>
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['error_message_try_again'], '
				</div>';

		return false;
	}
	// We're going to have to use... FTP!
	elseif ($failure)
	{
		// Load any session data we might have...
		if (!isset($_POST['ftp_username']) && isset($_SESSION['installer_temp_ftp']))
		{
			$_POST['ftp_server'] = $_SESSION['installer_temp_ftp']['server'];
			$_POST['ftp_port'] = $_SESSION['installer_temp_ftp']['port'];
			$_POST['ftp_username'] = $_SESSION['installer_temp_ftp']['username'];
			$_POST['ftp_password'] = $_SESSION['installer_temp_ftp']['password'];
			$_POST['ftp_path'] = $_SESSION['installer_temp_ftp']['path'];
		}

		if (isset($_POST['ftp_username']))
		{
			$ftp = new ftp_connection($_POST['ftp_server'], $_POST['ftp_port'], $_POST['ftp_username'], $_POST['ftp_password']);

			if ($ftp->error === false)
			{
				// Try it without /home/abc just in case they messed up.
				if (!$ftp->chdir($_POST['ftp_path']))
				{
					$ftp_error = $ftp->last_message;
					$ftp->chdir(preg_replace('~^/home[2]?/[^/]+?~', '', $_POST['ftp_path']));
				}
			}
		}

		if (!isset($ftp) || $ftp->error !== false)
		{
			if (!isset($ftp))
				$ftp = new ftp_connection(null);
			// Save the error so we can mess with listing...
			elseif ($ftp->error !== false && !isset($ftp_error))
				$ftp_error = $ftp->last_message === null ? '' : $ftp->last_message;

			list ($username, $detect_path, $found_path) = $ftp->detect_path(dirname(__FILE__));

			if ($found_path || !isset($_POST['ftp_path']))
				$_POST['ftp_path'] = $detect_path;

			if (!isset($_POST['ftp_username']))
				$_POST['ftp_username'] = $username;

			echo '
				<div class="panel">
					<h2>', $txt['ftp_setup'], '</h2>
					<h3>', $txt['ftp_setup_info'], '</h3>';

			if (isset($ftp_error))
				echo '
					<div class="error_message">
						<div style="color: red;">
							', $txt['error_ftp_no_connect'], '<br />
							<br />
							<code>', $ftp_error, '</code>
						</div>
					</div>
					<br />';

			echo '
					<form action="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true" method="post">

						<table width="520" cellspacing="0" cellpadding="0" border="0" align="center" style="margin-bottom: 1ex;">
							<tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_server">', $txt['ftp_server'], ':</label></td>
								<td>
									<div style="float: ', empty($txt['lang_rtl']) ? 'right' : 'left', '; margin-', empty($txt['lang_rtl']) ? 'right' : 'left', ': 1px;"><label for="ftp_port" class="textbox"><b>', $txt['ftp_port'], ':&nbsp;</b></label> <input type="text" size="3" name="ftp_port" id="ftp_port" value="', isset($_POST['ftp_port']) ? $_POST['ftp_port'] : '21', '" /></div>
									<input type="text" size="30" name="ftp_server" id="ftp_server" value="', isset($_POST['ftp_server']) ? $_POST['ftp_server'] : 'localhost', '" style="width: 70%;" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['ftp_server_info'], '</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_username">', $txt['ftp_username'], ':</label></td>
								<td>
									<input type="text" size="50" name="ftp_username" id="ftp_username" value="', isset($_POST['ftp_username']) ? $_POST['ftp_username'] : '', '" style="width: 99%;" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', $txt['ftp_username_info'], '</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_password">', $txt['ftp_password'], ':</label></td>
								<td>
									<input type="password" size="50" name="ftp_password" id="ftp_password" style="width: 99%;" />
									<div style="font-size: smaller; margin-bottom: 3ex;">', $txt['ftp_password_info'], '</div>
								</td>
							</tr><tr>
								<td width="26%" valign="top" class="textbox"><label for="ftp_path">', $txt['ftp_path'], ':</label></td>
								<td style="padding-bottom: 1ex;">
									<input type="text" size="50" name="ftp_path" id="ftp_path" value="', $_POST['ftp_path'], '" style="width: 99%;" />
									<div style="font-size: smaller; margin-bottom: 2ex;">', !empty($found_path) ? $txt['ftp_path_found_info'] : $txt['ftp_path_info'], '</div>
								</td>
							</tr>
						</table>

						<div style="margin: 1ex; margin-top: 2ex; text-align: ', empty($txt['lang_rtl']) ? 'right' : 'left', ';"><input type="submit" value="', $txt['ftp_connect'], '" /></div>
					</form>

					<h2>', $txt['ftp_setup_why'], '</h2>
					<h3>', $txt['ftp_setup_why_info'], '</h3>

					<ul style="margin: 2.5ex; font-family: monospace;">
						<li>', implode('</li>
						<li>', $writable_files), '</li>
					</ul>
					<a href="', $_SERVER['PHP_SELF'], '?step=0&amp;overphp=true">', $txt['error_message_click'], '</a> ', $txt['ftp_setup_again'], '

				</div>';

			return false;
		}
		else
		{
			$_SESSION['installer_temp_ftp'] = array(
				'server' => $_POST['ftp_server'],
				'port' => $_POST['ftp_port'],
				'username' => $_POST['ftp_username'],
				'password' => $_POST['ftp_password'],
				'path' => $_POST['ftp_path']
			);

			foreach ($writable_files as $file)
			{
				if (!is_writable(dirname(__FILE__) . '/' . $file))
					$ftp->chmod($file, 0755);
				if (!is_writable(dirname(__FILE__) . '/' . $file))
					$ftp->chmod($file, 0777);
			}

			$ftp->close();
		}
	}

	return true;
}

function updateSettingsFile($vars)
{
	// Modify Settings.php.
	$settingsArray = file(dirname(__FILE__) . '/Settings.php');

	// !!! Do we just want to read the file in clean, and split it this way always?
	if (count($settingsArray) == 1)
		$settingsArray = preg_split('~[\r\n]~', $settingsArray[0]);

	for ($i = 0, $n = count($settingsArray); $i < $n; $i++)
	{
		// Remove the redirect...
		if (trim($settingsArray[$i]) == 'if (file_exists(dirname(__FILE__) . \'/install.php\'))')
		{
			$settingsArray[$i] = '';
			$settingsArray[$i++] = '';
			$settingsArray[$i++] = '';
			continue;
		}
		elseif (substr(trim($settingsArray[$i]), -16) == '/install.php\');' && substr(trim($settingsArray[$i]), 0, 26) == 'header(\'Location: http://\'')
		{
			$settingsArray[$i] = '';
			continue;
		}

		if (trim($settingsArray[$i]) == '?' . '>')
			$settingsArray[$i] = '';

		// Don't trim or bother with it if it's not a variable.
		if (substr($settingsArray[$i], 0, 1) != '$')
			continue;

		$settingsArray[$i] = rtrim($settingsArray[$i]) . "\n";

		foreach ($vars as $var => $val)
			if (strncasecmp($settingsArray[$i], '$' . $var, 1 + strlen($var)) == 0)
			{
				$comment = strstr($settingsArray[$i], '#');
				$settingsArray[$i] = '$' . $var . ' = \'' . $val . '\';' . ($comment != '' ? "\t\t" . $comment : "\n");
				unset($vars[$var]);
			}
	}

	// Uh oh... the file wasn't empty... was it?
	if (!empty($vars))
	{
		$settingsArray[$i++] = '';
		foreach ($vars as $var => $val)
			$settingsArray[$i++] = '$' . $var . ' = \'' . $val . '\';' . "\n";
	}

	// Blank out the file - done to fix a oddity with some servers.
	$fp = @fopen(dirname(__FILE__) . '/Settings.php', 'w');
	if (!$fp)
		return false;
	fclose($fp);

	$fp = fopen(dirname(__FILE__) . '/Settings.php', 'r+');

	// Gotta have one of these ;).
	if (trim($settingsArray[0]) != '<?php')
		fwrite($fp, "<?php\n");

	$lines = count($settingsArray);
	for ($i = 0; $i < $lines - 1; $i++)
	{
		// Don't just write a bunch of blank lines.
		if ($settingsArray[$i] != '' || @$settingsArray[$i - 1] != '')
			fwrite($fp, strtr($settingsArray[$i], "\r", ''));
	}
	fwrite($fp, $settingsArray[$i] . '?' . '>');
	fclose($fp);

	return true;
}

// Create an .htaccess file to prevent mod_security. SMF has filtering built-in.
function fixModSecurity()
{
	$htaccess_addition = '
<IfModule mod_security.c>
	# Turn off mod_security filtering.  SMF is a big boy, it doesn\'t need its hands held.
	SecFilterEngine Off

	# The below probably isn\'t needed, but better safe than sorry.
	SecFilterScanPOST Off
</IfModule>';

	if (!function_exists('apache_get_modules') || !in_array('mod_security', apache_get_modules()))
		return true;
	elseif (file_exists(dirname(__FILE__) . '/.htaccess') && is_writable(dirname(__FILE__) . '/.htaccess'))
	{
		$current_htaccess = implode('', file(dirname(__FILE__) . '/.htaccess'));

		// Only change something if mod_security hasn't been addressed yet.
		if (strpos($current_htaccess, '<IfModule mod_security.c>') === false)
		{
			if ($ht_handle = fopen(dirname(__FILE__) . '/.htaccess', 'a'))
			{
				fwrite($ht_handle, $htaccess_addition);
				fclose($ht_handle);
				return true;
			}
			else
				return false;
		}
		else
			return true;
	}
	elseif (file_exists(dirname(__FILE__) . '/.htaccess'))
		return strpos(implode('', file(dirname(__FILE__) . '/.htaccess')), '<IfModule mod_security.c>') !== false;
	elseif (is_writable(dirname(__FILE__)))
	{
		if ($ht_handle = fopen(dirname(__FILE__) . '/.htaccess', 'w'))
		{
			fwrite($ht_handle, $htaccess_addition);
			fclose($ht_handle);
			return true;
		}
		else
			return false;
	}
	else
		return false;
}

?>