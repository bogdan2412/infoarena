<?php
// This module contains various database-related functions and routines.

// To avoid some name clashes with SMF, we have an alternate database API
// when working inside SMF.
if (defined("IA_FROM_SMF")) {
    require_once(IA_ROOT_DIR."common/db/db_smf_mysql.php");
}
else {
    require_once(IA_ROOT_DIR."common/db/db_mysql.php");
}

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
// NOTE: We cannot use strptime() since it doesn't work on windows
function db_date_parse($string) {
    // maybe it's a date&time
    $matches = null;
    $ret = preg_match('/^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$/',
                      $string, $matches);
    if ($ret) {
        return mktime($matches[4], $matches[5], $matches[6],
                      $matches[2], $matches[3], $matches[1]);
    }

    // probably just a date
    $ret = preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/',
                      $string, $matches);
    if ($ret) {
        return mktime(12, 0, 0, $matches[2], $matches[3], $matches[1]);
    }

    // unknown date format
    return false;
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

    if (count($rows) == 0) {
        return $default_value;
    }

    // failsafe
    log_assert(1 == count($rows), 'db_query_value() expects 1 row at most');
    $row = array_values($rows[0]);
    log_assert(1 == count($row), 'db_query_value() expects 1 column at most');

    return $row[0];
}

// Retries the query for $retries times or until it succeeded.
// Wrapper for db_query.
// Returns native PHP mysql resource handle.
function db_query_retry($query, $retries) {
    $result = false;

    for ($try = 0; $try <= $retries && !$result; ++$try) {
        $result = db_query($query);
    }

    return $result;
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

    $query = "INSERT INTO `{$table}` (`";
    $query .= join('`, `', array_keys($dict));
    $query .= "`) VALUES (";
    $query .= join(", ", array_map('db_quote', array_values($dict)));
    $query .= ")";

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
            $v = db_quote($v);
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
                      WHERE `name` LIKE 'stiri/%s%%'
                      ORDER BY ia_textblock.`creation_timestamp` DESC
                      LIMIT %s, %s",
                     db_escape($prefix), db_quote((int)$start), db_quote((int)$range));
    return db_fetch_all($query);
}

function news_count($prefix = null) {
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock
                      WHERE `name` LIKE 'stiri/%s%%'",
                      db_escape($prefix));
    $tmp = db_fetch($query);
    return $tmp['cnt'];
}

// Quotes a variable so it can be safely placed inside an SQL query.
// This will surround strings with quotes and leave integers alone.
//
// NOTE: this function is always safe to concat inline.
function db_quote($arg) {
    if (is_null($arg)) {
        return 'NULL';
    } else if (is_string($arg)) {
        return "'" . db_escape($arg) . "'";
    } else if (is_numeric($arg)) {
        // FIXME: is_numeric guarantees mysql safety?
        // FIXME: does it also guarantee that mysql can parse it?
        return (string)$arg;
        //return "'" . db_escape((string)$arg) . "'";
    } else if (is_bool($arg)) {
        if ($arg) {
            return 'TRUE';
        } else {
            return 'FALSE';
        }
    } else if (is_array($arg) || is_object($arg) || is_resource($arg) || is_callable($arg)) {
        log_error("Can't db_quote complex objects");
        return (string)$arg;
    } else {
        log_error("Unknown object type?");
    }
}

// Escape an array of strings.
function db_escape_array($array) {
    $ret = implode(',', array_map('db_quote', $array));

    return $ret;
}

// Executes query, fetches only FIRST result row
function db_fetch($query) {
    $result = db_query($query, true);
    if ($result) {
        $row = db_next_row($result);
        if ($row === false) {
            db_free($result);
            return null;
        }
        db_free($result);
        return $row;
    } else {
        return null;
    }
}

// FIXME: This shouldn't be here. Move it in common/db/task.php or
// common/db/round.php
function db_get_task_filter_clause($filter, $table_alias) {
    if ($filter == IA_TLF_SOLVED) {
        return "{$table_alias}.score = 100";
    } else if ($filter == IA_TLF_TRIED) {
        return "{$table_alias}.score < 100";
    } else if ($filter == IA_TLF_UNSOLVED) {
        return "{$table_alias}.score is null";
    } else {
        return '1';
    }
}

