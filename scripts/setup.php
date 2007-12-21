#! /usr/bin/env php
<?php

// Duplicated, this script has no includes.

// Asks the user a question.
// $default is the default answer
function read_line($question, $default = null) {
    if ($default === null) {
        echo "$question ";
    } else {
        echo "$question"." (default:$default) ";
    }
    $r = trim(fgets(STDIN));
    if ($r == "") {
        $r = $default;
    }
    return $r;
}

// Same as read_line, but returns true/false.
// default must be true/false, or null.
function read_bool($question, $default = null) {
    while (true) {
        if ($default === null) {
            $answer = read_line($question);
        } else if ($default) {
            $answer = read_line($question, "yes");
        } else {
            $answer = read_line($question, "no");
        }
        if (preg_match("/^(true|y|yes|da)$/i", $answer)) {
            return true;
        }
        if (preg_match("/^(false|n|no|nu)$/i", $answer)) {
            return false;
        }
        echo "Answer with true/false/yes/no/etc.\n";
    }
}

// Add slashes to a string.
function slash_string($string, $start, $end)
{
    // Strip start/end slash
    if ($string[0] == '/') {
        $string = substr($string, 1);
    }
    if ($string[strlen($string) - 1] == '/') {
        $string = substr($string, 0, strlen($string) - 1);
    }

    // Add start/end slash
    if ($start) {
        $string = '/'.$string;
    }
    if ($end) {
        $string = $string.'/';
    }

    // Tada.
    return $string;
}

// Checks if the script is running as root.
function running_as_root()
{
    return posix_getuid() == 0;
}

// Replace config values in $srcfile and copy to $dstfile
function handle_config_file($vars, $srcfile, $dstfile)
{
    $contents = file_get_contents($srcfile);
    foreach ($vars as $k => $v) {
        $contents = str_replace("--write-me-$k--", $v, $contents);
    }
    file_put_contents($dstfile, $contents);

    // FIXME perms when running as root
    if (running_as_root()) {
        chown($dstfile, getmyuid());
        chgrp($dstfile, getmygid());
    }
}

// ---
// Real code starts here
// ---

print("\nHello, I am the infoarena setup script and I will try to guide ".
          "you through setting up an infoarena developement working copy.\n");

if (running_as_root()) {
    print("I see you are running as root, this is good.\n");
} else {
    print("You are not root. This is fine with me, but you'll probably ".
        "have to do some thing by hand(as root)\n");
}

print("I'm going to ask you a couple of questions. Just press enter if you like ".
      "the default value. I'm pretty good at guessing ;).\n\n");

// FIXME: Check dependencies? pecl nonsense is no longer mandatory.
// It should be done it setup.sh anyway, since we can't relie on php-cli.

// Initialize config vars.
$config_vars = array();
$config_vars['IA_ROOT_DIR'] = realpath(dirname($argv[0]) . '/../') . '/';
$config_vars['IA_URL_HOST'] = 'http://localhost';
$config_vars['IA_URL_PREFIX'] = '/infoarena2-dev/';
$config_vars['IA_DB_HOST'] = 'localhost';
$config_vars['IA_DB_USER'] = 'root';
$config_vars['IA_DB_PASS'] = '';
$config_vars['IA_DB_NAME'] = 'infoarena2';

// Ask user.
$config_vars['IA_ROOT_DIR'] = read_line("SVN checkout dir?", $config_vars['IA_ROOT_DIR']);
$config_vars['IA_ROOT_DIR'] = slash_string(
        realpath($config_vars['IA_ROOT_DIR']), true, true);
$config_vars['IA_URL_HOST'] = read_line("Host part of url (with http)?",
        $config_vars['IA_URL_HOST']);
$config_vars['IA_URL_HOST'] = slash_string(
        $config_vars['IA_URL_HOST'], false, false);
$config_vars['IA_URL_PREFIX'] = read_line("Prefix part of url?",
        $config_vars['IA_URL_PREFIX']);
$config_vars['IA_URL_PREFIX'] = slash_string(
        $config_vars['IA_URL_PREFIX'], true, true);

// Database configuration here.
while (true) {
    $config_vars['IA_DB_HOST'] = read_line("Database host?",
            $config_vars['IA_DB_HOST']);
    $config_vars['IA_DB_USER'] = read_line("Database connection username?",
            $config_vars['IA_DB_USER']);
    $config_vars['IA_DB_PASS'] = read_line("Database password?",
            $config_vars['IA_DB_PASS']);
    $config_vars['IA_DB_NAME'] = read_line("Database name?",
            $config_vars['IA_DB_NAME']);
    // FIXME: check database connection.

    $dblink = mysql_connect(
            $config_vars['IA_DB_HOST'],
            $config_vars['IA_DB_USER'],
            $config_vars['IA_DB_PASS']);

    if (!$dblink) {
        print("Can't connect to database, something must be wrong.\n");
        if (read_bool("Try again or ignore (CTRL-C to abort)?", true)) {
            continue;
        } else {
            break;
        }
    }

    if (!mysql_select_db($config_vars['IA_DB_NAME'], $dblink)) {
        print("Can't select database.\n");
        if (read_bool("Should I try to create the database?", true)) {
            if (!mysql_query("CREATE DATABASE {$config_vars['IA_DB_NAME']}")) {
                die("Failed creating database, sorry.");
            }
            if (!mysql_select_db($config_vars['IA_DB_NAME'], $dblink)) {
                die("Still can't select database.\n");
            }
        }
    }
    break;
}

// Do the config monkey.
$ia_root = $config_vars['IA_ROOT_DIR'];
$ia_url = $config_vars['IA_URL_HOST'] . $config_vars['IA_URL_PREFIX'];
handle_config_file($config_vars,
        $ia_root.'config.php.sample', $ia_root.'config.php');
handle_config_file($config_vars,
        $ia_root.'www/htaccess.sample', $ia_root.'www/.htaccess');
handle_config_file($config_vars,
        $ia_root.'eval/config.php.sample', $ia_root.'eval/config.php');
handle_config_file($config_vars,
        $ia_root.'apache.conf.sample', $ia_root.'apache.conf');

if ($dblink && read_bool("Should I try to import the sample database?", true)) {
    $cmd = sprintf("mysql --user=%s --password=%s --host=%s %s < %s",
            $config_vars['IA_DB_USER'],
            $config_vars['IA_DB_PASS'],
            $config_vars['IA_DB_HOST'],
            $config_vars['IA_DB_NAME'],
            $config_vars['IA_ROOT_DIR'] . "db.sql");
    print("Running $cmd\n");
    system($cmd);
}

// Configure apache
if (!running_as_root()) {
    print("I'd try to configure apache, but I'm not root.\n");
}
if (running_as_root() &&
        read_bool("Should I try to configure apache for you?", true)) {
    $sitename = slash_string($config_vars['IA_URL_PREFIX'], false, false);
    $sitename = read_line("Site name?", $sitename);
    system("chmod g+ws {$ia_root}attach");
    system("chmod g+ws {$ia_root}cache");
    system("chmod g+ws {$ia_root}www/static/images/tmp");
    system("chmod g+ws {$ia_root}www/static/images/latex");

    $username = null;
    if (preg_match('/\/home\/([^\/]*)/', IA_ROOT, $matches)) {
        $username = $matches[1];
    }
    // Debian/Ubuntu
    if (is_dir('/etc/apache2/sites-available/') &&
        is_dir('/etc/apache2/sites-enabled/')) {
        print("You seem to have a debian-ish apache2 setup.\n");
        system("rm -rf /etc/apache2/sites-available/$sitename");
        system("rm -rf /etc/apache2/sites-enabled/$sitename");
        system("ln -sf {$ia_root}apache.conf ".
                "/etc/apache2/sites-available/$sitename");

        // Enable site ad modules, then reload
        system("a2enmod php5");
        system("a2enmod rewrite");
        system("a2ensite $sitename");
        system("/etc/init.d/apache2 reload");
        if ($username != null) {
            system("adduser www-data {$username}");
        }
    // Fedora/Redhat/others?
    } else if (is_dir('/etc/httpd/conf.d/')) {
        print('You seem to have a redhat-ish apache2 setup.\n');
        system("rm -rf /etc/httpd/infoarena2-dev");
        system("ln -sf {$ia_root}apache.conf /etc/httpd/conf.d/$sitename");
        system("service httpd restart");
        if ($username != null) {
            system("usermod -G `id -g $username` -a apache");
        }
    } else {
        print("I can't figure out your system. I'm scared.\n");
    }
}

// FIXME: configure forum
if (read_bool("Should I try to configure the forum (ugly db stuff)?", true)) {
    system("{$ia_root}scripts/forum-fix");
}

print("FIXME: forum is not completely functional\n");
print("FIXME: eval won't work, but it doesn't matter.\n");
