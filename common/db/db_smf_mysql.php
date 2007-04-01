<?php

// Connects to the database. Call this function if you need the database.
// It's better than connecting when the file is included. Side-effects are bad.
function db_connect() {
    // SMF is already connected to the database
}

// Escapes a string to be safely included in a query.
function db_escape($str) {
    return mysql_real_escape_string($str);
}

// Number of rows selected by the last SELECT statement
function db_num_rows($res) {
    return mysql_num_rows($res);
}

// Frees mysql result
function db_free($result) {
    log_assert(is_resource($result));
    mysql_free_result($result);
}

// Fetches next result row
function db_next_row($result) {
    return mysql_fetch_assoc($result);
}

// NOTE: Already defined in SMF
// Returns last SQL inserted id
// function db_insert_id();

// NOTE: Already defined in SMF
// Returns number of affected rows by the last UPDATE/INSERT statement
// function db_affected_rows();

// Executes query. Outputs error messages
// Returns native PHP mysql resource handle
// function db_query($query, $unbuffered = false);

?>
