#! /usr/bin/env php
<?php

/**
 * Deletes a user and most of their data (jobs, round participations etc.)
 *
 * Keeps some data, reassigning it to another user:
 *   - tasks created by the user;
 *   - rounds created by the user;
 *   - attachments uploaded by the user (other than their avatar);
 *   - wiki pages created by the user (other than their user profile).
 *
 * Logs everything to stdout. Please consider running this script with | tee...
 * in order to keep a copy of the log.
 *
 * Please run this script as root. It needs to delete avatar files.
 *
 * Remember to run scripts/recompute-ratings at the end.
 *
 * Asks for confirmation before it proceeds. You have one chance to change
 * your mind. These actions are irreversible!
 */

require_once(__DIR__ . '/utilities.php');
require_once(__DIR__ . '/../common/avatar.php');
require_once(__DIR__ . '/../common/db/user.php');
db_connect();

(count($argv) == 3) || usage($argv[0]);

$user = user_get_by_username($argv[1]);
$user || error("User {$argv[1]} not found.");
$target = user_get_by_username($argv[2]);
$target || error("User {$argv[2]} not found.");

($user['id'] != $target['id']) ||
    error('Please specify two different users.');

($user['security_level'] != 'admin') ||
    error('This script refuses to delete admins. Please comment out this ' .
          'check if you are really sure.');

// last chance to change your mind
confirmation($user, $target);

delete_user($user, $target);

/*************************************************************************/

function usage(string $cmd) {
    error("Usage: php $cmd <username> <dest-username>

Deletes the user with the given username and most of their data.
Transfers some of the user's data to another user specified by <dest-username>.
Please read the comments at the beginning of the PHP file for details.");
}

function error(string $msg) {
    print "$msg\n";
    exit(1);
}

function confirmation(array $user, array $target) {
    // warn that the end of the world is coming
    printf("You are about to delete most data for user:

id=%d, username=%s, full_name=%s

We will keep some data (tasks / rounds / attachments / wiki pages) and migrate it to this user:

id=%d, username=%s, full_name=%s

These actions are IRREVERSIBLE. This script will log everything it does to stdout.
Please consider running it with | tee <file> to save a copy of the log.
Please read the comments in the PHP file for details about what the script does.

",
           $user['id'], $user['username'], $user['full_name'],
           $target['id'], $target['username'], $target['full_name']);

    $answer = readline('Is this OK? Type "yes" to proceed, anything else to abort: ');
    if ($answer !== 'yes') {
        error('Aborting.');
    }
}

/**
 * Actual deletion happens here.
 */
function delete_user(array $user, array $target) {
    // List tables which contain user_id references which can simply be deleted.
    // For every such table, enumerate fields to log as we delete them.
    $FOREIGN_DATA = [
        'ia_acm_round',
        'ia_rating',
        'ia_task_users_solved',
        'ia_score_task_top_users',
        'ia_score_user_round',
        'ia_score_user_round_task',
        'ia_user_round',
        'ia_user_tags',
        'task_peep',
    ];

    foreach ($FOREIGN_DATA as $table) {
        delete_from_table_by_user_id($user['id'], $table);
    }

    delete_jobs($user['id']);
    delete_files($user);
    delete_textblocks('ia_textblock', $user);
    delete_textblocks('ia_textblock_revision', $user);
    avatar_delete($user['username']);

    $MIGRATIONS = [
        'ia_file' => [],
        'ia_round' => [],
        'ia_task' => [],
        'ia_task_ratings' => [],
        'ia_textblock' => [ 'text' ],
        'ia_textblock_revision' => [ 'text' ],
    ];
    foreach ($MIGRATIONS as $table => $excluded_fields) {
        migrate_table($table, $user['id'], $target['id'], $excluded_fields);
    }

    pretend_query('delete from ia_user where id = ' . $user['id']);
}

/**
 * Deletes records from $table. Assumes that $table has a user_id field.
 */
function delete_from_table_by_user_id(int $user_id, string $table) {
    $stub = "from {$table} where user_id = {$user_id}";
    $res = db_fetch_all(build_select_query($stub));
    foreach ($res as $row) {
        log_table_row($table, $row);
    }

    pretend_query(build_delete_query($stub));
}

/**
 * Deletes a user's jobs with all their test cases.
 */
function delete_jobs(int $user_id) {
    $stub = "from ia_job where user_id = {$user_id}";
    $res = db_fetch_all(build_select_query($stub));
    foreach ($res as $job) {
        log_table_row('ia_job', $job, [ 'eval_log', 'file_contents' ]);

        $test_stub = 'from ia_job_test where job_id = ' . $job['id'];
        $test_res = db_fetch_all(build_select_query($test_stub));

        foreach ($test_res as $test) {
            log_table_row('ia_job_test', $test, [ 'eval_log', 'file_contents' ]);
        }

        pretend_query(build_delete_query($test_stub));
    }

    pretend_query(build_delete_query($stub));
}

/**
 * Deletes certain records from ia_file.
 */
function delete_files(array $user) {
    $stub = sprintf(
        'from ia_file where user_id = %d and name = "avatar" and page = "utilizator/%s"',
        $user['id'],
        addslashes($user['username']));
    $res = db_fetch_all(build_select_query($stub));
    foreach ($res as $row) {
        @unlink(attachment_get_filepath($row)); // delete the underlying file
        log_table_row('ia_file', $row);
    }

    pretend_query(build_delete_query($stub));
}

/**
 * Deletes certain records from ia_textblock and ia_textblock_revision
 */
function delete_textblocks(string $table, array $user) {
    $stub = sprintf(
        'from %s where user_id = %d and name = "utilizator/%s"',
        $table,
        $user['id'],
        addslashes($user['username']));
    $res = db_fetch_all(build_select_query($stub));
    foreach ($res as $row) {
        log_table_row($table, $row, [ 'text' ]);
    }

    pretend_query(build_delete_query($stub));
}

/**
 * Migrates records from $table from user_id = $user_id to $target_id.
 */
function migrate_table(string $table, int $user_id, int $target_id, $excluded_fields) {
    $stub = "from {$table} where user_id = {$user_id}";
    $res = db_fetch_all(build_select_query($stub));

    foreach ($res as $row) {
        log_table_row($table, $row, $excluded_fields, 'migrate');
    }

    pretend_query(build_update_query($table, $user_id, $target_id));
}

/**
 * Logs a table row that's about to be deleted or migrated. Lists every field
 * and its value, except those in $excluded_fields.
 */
function log_table_row(
    string $table, array $row, array $excluded_fields = [], string $op = 'delete') {

    print "will {$op} row from {$table} |";
    foreach ($row as $field => $value) {
        if (!in_array($field, $excluded_fields)) {
            print " $field=$value";
        }
    }
    print "\n";
}

/**
 * Runs a SQL query. Same as db_query(), but easier to intercept and comment
 * out for debugging purposes.
 */
function pretend_query(string $query) {
    print "Running SQL query: $query\n";
    db_query($query);
}

/**
 * Builds a select query that will have a corresponding delete query. Really
 * trivial, but it makes sure that we don't log one thing and delete another.
 * @param string $stub A query starting with "from <table>..."
 */
function build_select_query(string $stub) {
    return 'select * ' . $stub;
}

/**
 * Builds a delete query that will have a corresponding select query. Really
 * trivial, but it makes sure that we don't log one thing and delete another.
 * @param string $stub A query starting with "from <table>..."
 */
function build_delete_query(string $stub) {
    return 'delete ' . $stub;
}

/**
 * Builds an update query that migrates the user_id field. Really trivial, but
 * factored out for consistency.
 */
function build_update_query(string $table, int $user_id, int $target_id) {
    return "update {$table} set user_id = {$target_id} where user_id = {$user_id}";
}
