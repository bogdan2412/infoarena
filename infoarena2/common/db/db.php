<?php
// This module contains various database-related functions and routines.

// When including infoarena API from SMF, it is required to skip this
// module as it clashes with SMF's db_* functions.
// FIXME: Find a better hack
if (defined("IA_FROM_SMF")) {
    return;
}

// We currently use mysql
// This also connects to mysql server
require_once(IA_ROOT_DIR."common/db/db_mysql.php");

// Executes query, fetches the all result rows
function db_fetch_all($query) {
    $result = db_query($query, true);
    if ($result) {
        $buffer = array();
        while ($row = db_next_row($result)) {
            $buffer[] = $row;
        }
        db_free($result);
        return $buffer;
    } else {
        return null;
    }
}

// tells whether given string is a valid datetime value
// see parse_datetime()
function is_db_date($string) {
    $timestamp = db_date_parse($string);
    return (false !== $timestamp);
}

// parse value of a datetime parameter in SQL format.
// i.e.: 2006-11-27 23:59:59
//
// returns unix timestamp or FALSE upon error
function db_date_parse($string) {
    $res = strptime($string, '%Y-%m-%d %T');

    if (!$res) {
        return false;
    }

    return mktime($res['tm_hour'], $res['tm_min'], $res['tm_sec'],
                  1, $res['tm_yday']+1, $res['tm_year']+1900);
}

// formats unix timestamp as a datetime parameter value, suitable for SQL.
// i.e.: 2006-11-27 23:59:59
//
// NOTE: prefer db_date_format() to NOW().
// NOW returns the current time in the database server's timezone.
//
// All times in the database are UTC!!!
function db_date_format($timestamp = null) {
    if ($timestamp === null) {
        $res = strftime('%Y-%m-%d %T');
    } else {
        $res = strftime('%Y-%m-%d %T', $timestamp);
    }

    return $res;
}

// Executes SQL query and returns value of the first column in the first
// result row.
// When query yields no results, it returns $default_value
//
// WARNING: This function asserts there is at most 1 result row and 1 column.
function db_query_value($query, $default_value = null) {
    global $dbLink;

    $rows = db_fetch_all($query);

    if (is_null($rows)) {
        return $default_value;
    }

    // failsafe
    log_assert(1 == count($rows), 'db_query_value() expects 1 row at most');
    $row = array_values($rows[0]);
    log_assert(1 == count($row), 'db_query_value() expects 1 column at most');

    return $row[0];
}

// Executes SQL INSERT statement (wrapper for db_query)
// Returns last SQL insert id
//
// Arguments:
// $table   SQL table name
// $dict    dictionary of fields to insert
//
// Example:
// $user = array(
//      'full_name' => 'Gigi Kent',
//      'username' => 'gigikent'
// );
// db_insert('user', $user);
//
// will execute:
// INSERT INTO `user` (`full_name`, `username`)
// VALUES ('Gigi Kent', 'gigikent')
//
// Returns last insert-ed primary key value
function db_insert($table, $dict) {
    global $dbLink;

    foreach ($dict as $k => $v) {
        if (is_null($v)) {
            unset($dict[$k]);
        }
    }

    $table = db_escape($table);

    $query = "INSERT INTO `{$table}` (`";
    $query .= join('`, `', array_keys($dict));
    $query .= "`) VALUES ('";
    $query .= join("', '", array_map('db_escape', array_values($dict)));
    $query .= "')";

    db_query($query);

    return db_insert_id();
}

// Executes SQL UPDATE statement (wrapper for db_query)
// Returns number of affected rows
//
// PHP null values are expanded to SQL NULL
//
// Arguments:
// $table   SQL table name
// $dict    dictionary of fields to update
// $where   pre-escaped WHERE clause to be inserted inline
//
// Example:
// $user = array(
//      'full_name' => 'Gigi Kent',
//      'password' => 'xxx'
// );
// db_update('user', $user, "username='wickedman'");
//
// will execute:
// UPDATE `user`
// SET `full_name` = 'Gigi Kent', `password` = 'xxx'
// WHERE username='wickedman'
function db_update($table, $dict, $where = null) {
    global $dbLink;

    // fail safe
    log_assert(1 <= count($dict), 'db_update() called with empty $dict');

    // build query
    $table = db_escape($table);
    $query = "UPDATE `{$table}`\nSET ";
    $first = true;
    foreach ($dict as $k => $v) {
        //  - comma
        if (!$first) {
            $query .= ', ';
        }
        $first = false;

        //  - field-value pair
        if (is_null($v)) {
            $v = 'NULL';
        }
        else {
            $v = "'".db_escape($v)."'";
        }
        $query .= "`{$k}` = {$v}";
    }
    //  - WHERE clause
    if (!is_null($where)) {
        $query .= " WHERE ".$where;
    }

    db_query($query);

    return db_affected_rows();
}

// FIXME: obliterate
/**
 * News
 * This is for the special "news" controller.
 */
function news_get_range($start, $range, $prefix = null) {
    $query = sprintf("SELECT
                        *
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'stiri/%s%%'
                      ORDER BY ia_textblock.`timestamp` DESC
                      LIMIT %s,%s",
                     db_escape($prefix), db_escape($start), db_escape($range));
    return db_fetch_all($query);
}

function news_count($prefix = null) {
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'stiri/%s%%'",
                      db_escape($prefix));
    $tmp = db_fetch($query);
    return $tmp['cnt'];
}

?>
