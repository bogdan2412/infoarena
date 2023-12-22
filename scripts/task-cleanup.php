#! /usr/bin/env php
<?php

// Without arguments, goes through every record in the task table and reports
// some issues:
// - Task is invalid according to task_validate().
// - Task has no corresponding textblock.
// - Task has no attached files (test data etc.).
// - Task has no jobs submitted.
//
// With the -d <task_id> option, deletes the task.

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/utilities.php';
require_once __DIR__ . '/../common/db/task.php';
require_once __DIR__ . '/../lib/Core.php';

db_connect();

// parse the -d command line argument
$opts = getopt('d:');
$to_delete = $opts['d'] ?? false;

if ($to_delete) {
    maybeDeleteTask($to_delete);
    exit;
}

$attachments = db_fetch_all('select distinct page from ia_file where page like "problema/%"');
$has_attachments = [];
foreach ($attachments as $a) {
    $has_attachments[$a['page']] = true;
}

$tasks = db_fetch_all('select * from ia_task order by id');
foreach ($tasks as $t) {
    // collect errors from task_validate()
    $validation_errors = task_validate($t);

    $errors = [];

    // error: no textblock
    $textblock = db_fetch(sprintf('select * from ia_textblock where name = %s',
                                  db_quote($t['page_name'])));
    if (!$textblock) {
        $errors[] = '    * Nu are textblock (pagină wiki).';
    }
    $dateString = getDateString($t);

    // error: no attachments
    if (!isset($has_attachments[$t['page_name']])) {
        $errors[] = '    * Nu are fișiere atașate.';
    }

    // error: no jobs submitted
    $numJobs = db_query_value(sprintf('select count(*) as c from ia_job where task_id = %s',
                                      db_quote($t['id'])));
    if (!$numJobs) {
        $errors[] = '    * Nu are surse trimise.';
    }

    if (!empty($errors) || !empty($validation_errors)) {
        $user = user_get_by_id($t['user_id']);
        printf("* Problema [%s](%s%s%s) (autor:%s) %s(%d erori)\n",
               $t['id'],
               Config::URL_HOST,
               Config::URL_PREFIX,
               $t['page_name'],
               $user['username'],
               $dateString,
               count($errors) + count($validation_errors));
        foreach ($validation_errors as $field => $msg) {
            printf("    * task_validate() %s: %s\n", $field, $msg);
        }
        foreach ($errors as $msg) {
            print("$msg\n");
        }
    }
}

/*************************************************************************/

function maybeDeleteTask($task_id): void {
    $t = task_get($task_id);
    if (!$t) {
        die("Problema nu există.\n");
    }
    if (!empty(task_validate($t))) {
        die("Există erori de validare.\n");
    }
    printf("ȘTERG PROBLEMA\n");
    task_delete($t);
}

function getDateString(array $task): string {
    $revs = textblock_get_revision_list($task['page_name']);
    if (empty($revs)) {
        return '';
    }

    $first = $revs[0];
    $firstDate = getDateOnly($first['timestamp']);
    $last = $revs[count($revs) - 1];
    $lastDate = getDateOnly($last['timestamp']);

    $msg = sprintf('(creată: %s, modificată: %s) ', $firstDate, $lastDate);
    return $msg;
}

function getDateOnly(string $dateTime): string {
    $parts = explode(' ', $dateTime);
    return $parts[0];
}
