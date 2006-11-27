<?php

// Establish database connection
// Repetitive include guard. Is this really needed?
log_assert(!isset($dbLink));
log_print("connecting to database");
$dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS) or log_error('Cannot connect to database.');
mysql_select_db(DB_NAME, $dbLink) or log_error('Cannot select database.');

// Escapes a string to be safely included in a query.
function db_escape($str) {
    return mysql_escape_string($str);
}

// Number of rows selected by the last SELECT statement
function db_num_rows($res) {
    return mysql_num_rows($res);
}

// Returns last SQL inserted id
function db_insert_id() {
    global $dbLink;

    log_assert($dbLink);
    return mysql_insert_id($dbLink);
}

// Returns number of affected rows by the last UPDATE/INSERT statement
function db_affected_rows() {
    global $dbLink;

    log_assert($dbLink);
    return mysql_affected_rows($dbLink);
}

// Executes query. Outputs error messages
// Returns native PHP mysql resource handle
function db_query($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
    if (!$result) {
        log_print("Query: '$query'");
        log_error("MYSQL error: ".mysql_error($dbLink));
    }
    return $result;
}

// Executes query, fetches only FIRST result row
function db_fetch($query) {
    $result = db_query($query);
    if ($result) {
        $row = db_next_row($result);
        if ($row === false) {
            return null;
        }
        return $row;
    }
    else {
        return null;
    }
}

// Fetches next result row
function db_next_row($result) {
    return mysql_fetch_assoc($result);
}

?>
