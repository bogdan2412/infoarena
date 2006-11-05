<?php
// This module contains various database-related functions and routines.
//
// Note: We keep database-persisted "models" very simple. Most of them are
// simple dictionaries. 

require_once("../common/log.php");

// Establish database connection
// Repetitive include guard. Is this really needed?
log_assert(!isset($dbLink));
$dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS) or log_error('Cannot connect to database.');
mysql_select_db(DB_NAME, $dbLink) or die ('Cannot select database.');

// Escapes a string to be safely included in a query.
function db_escape($str) {
    return mysql_escape_string($str);
}

function db_num_rows($res) {
    return mysql_num_rows($res);
}

// Executes query. Outputs error messages
// Returns native PHP mysql resource handle
function db_query($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
    if (!$result) {
        log_error('MYSQL error: '.mysql_error($dbLink).'\n');
        log_error('Query: \''.$query.'\'\n');
    }
    return $result;
}

// Executes query, fetches only FIRST result
function db_fetch($query) {
    global $dbLink;
    $result = db_query($query);
    if ($result) {
        $row = mysql_fetch_assoc($result);
        if ($row === false) {
            return null;
        }
        return $row;
    }
    else {
        return null;
    }
}

// Executes query, fetches the whole result
function db_fetch_all($query) {
    global $dbLink;
    $result = db_query($query);
    if ($result) {
        $buffer = array();
        while ($row = mysql_fetch_assoc($result)) {
            $buffer[] = $row;
        }
        return $buffer;
    }
    else {
        return null;
    }
}

// Include actual db functions.
require_once(IA_ROOT . "common/db/job.php");
require_once(IA_ROOT . "common/db/round.php");
require_once(IA_ROOT . "common/db/task.php");
require_once(IA_ROOT . "common/db/textblock.php");
require_once(IA_ROOT . "common/db/user.php");
require_once(IA_ROOT . "common/db/score.php");
require_once(IA_ROOT . "common/db/attachment.php");

/**
 * Parameter
 * FIXME: This is sort of shared between rounds and tasks.
 */

// Lists all parameters of $type `type`.
// $type is "task" or "round"
function parameter_list($type) {
    $query = sprintf("SELECT * FROM ia_parameter WHERE `type` = '%s'",
                     db_escape($type));
    $dict = array();
    foreach (db_fetch_all($query) as $row) {
        $dict[$row['id']] = $row;
    }
    return $dict;
}

// Replaces all parameter values according to the given dictionary
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $object_type is "task" or "round"
function parameter_update_values($object_type, $object_id, $dict) {
    log_assert($object_type == 'task' || $object_type == 'round');

    // delete all parameters connected to this task
    $query = sprintf("DELETE FROM ia_parameter_value
                      WHERE object_type = '%s' AND object_id = LCASE('%s')",
                     db_escape($object_type), db_escape($object_id));
    db_query($query);

    // insert given parameters
    foreach ($dict as $k => $v) {
        $query = sprintf("INSERT INTO ia_parameter_value
                            (object_type, object_id, parameter_id, `value`)
                          VALUES ('%s', '%s', '%s', '%s')",
                         db_escape($object_type), db_escape($object_id),
                         db_escape($k), db_escape($v));
        db_query($query);
    }
}

// Returns hash with task parameter values
function parameter_get_values($object_type, $object_id) {
    $query = sprintf("SELECT *
                      FROM ia_parameter_value
                      WHERE object_type = '%s' AND object_id = LCASE('%s')",
                     db_escape($object_type), db_escape($object_id));
    $dict = array();
    foreach (db_fetch_all($query) as $row) {
        $dict[$row['parameter_id']] = $row['value'];
    }
    return $dict;
}

// Returns bool whether $value is a valid parameter value
function parameter_validate($parameter, $value) {
    return !$parameter['validator'] || preg_match($parameter['validator'], $value);
}

/**
 * News
 * FIXME: where does this belong?
 */
function news_get_range($start, $range, $prefix = null) {
    $query = sprintf("SELECT
                        *
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'news/%s%%'
                      ORDER BY ia_textblock.`timestamp` DESC
                      LIMIT %s,%s",
                     db_escape($prefix), db_escape($start), db_escape($range));
    return db_fetch_all($query);
}

function news_count() {
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'news/%%'");
    $tmp = db_fetch($query);
    return $tmp['cnt'];
}

?>
