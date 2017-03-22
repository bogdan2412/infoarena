#! /usr/bin/env php
<?php

//
require_once dirname($argv[0]).'/../scripts/utilities.php';

compile_lcs(IA_ROOT_DIR);
while (true) {
    $dblink = mysqli_connect(IA_DB_HOST, IA_DB_USER, IA_DB_PASS);

    if (!$dblink) {
        print("Can't connect to database, something must be wrong.\n");
        print("Maybe database container wasn't started?");
        if (read_bool('Try again or ignore (CTRL-C to abort)?', true)) {
            continue;
        } else {
            break;
        }
    }

    if (!mysqli_select_db($dblink, IA_DB_NAME)) {
        print("Can't select database\n");
        if (read_bool('Should I try to create the database?', true)) {
            if (!mysqli_query($dblink, 'CREATE DATABASE '.IA_DB_NAME)) {
                print("Failed creating database, sorry.\n");
                continue;
            }
            if (!mysqli_select_db($dblink, IA_DB_NAME)) {
                print("Still can't select database\n");
                continue;
            }
        }
    }
    break;
}

if ($dblink && read_bool('Should I try to import the sample database?', true)) {
    $cmd = sprintf(
        'mysql --user=%s --password=%s --host=%s %s < %s',
        escapeshellarg(IA_DB_USER),
        escapeshellarg(IA_DB_PASS),
        escapeshellarg(IA_DB_HOST),
        escapeshellarg(IA_DB_NAME),
        escapeshellarg(IA_ROOT_DIR.'db.sql'));
    print("Running $cmd\n");
    system($cmd);
    print("Done importing sample database\n\n");
}

if (read_bool('Should I try to build the avatar cache folder?', true)) {
    print("Building the avatar cache folder\n");
    passthru(IA_ROOT_DIR.'scripts/make-avatar-folder');
    print("Done building the avatar cache folder\n\n");
}

if (read_bool('Should I try to configure the forum (ugly db stuff)?', true)) {
    system(IA_ROOT_DIR.'scripts/forum-fix');
    print('Done configuring the forum');
}
