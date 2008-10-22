<?php

// Connects to the database. Call this function if you need the database.
// It's better than connecting when the file is included. Side-effects are bad.
function db_connect() {
    global $dbLink;
    // Repetitive include guard. Is this really needed?
    log_assert(!isset($dbLink), "Already connected to the database.");

    // log_print("connecting to database");
    if (!$dbLink = @mysql_connect(IA_DB_HOST, IA_DB_USER, IA_DB_PASS)) {
        log_error('Cannot connect to database: '.mysql_error());
    }
    if (!mysql_select_db(IA_DB_NAME, $dbLink)) {
        log_error('Cannot select database.');
    }
    mysql_query('SET NAMES utf8');
}

function db_isalive() {
    global $dbLink;

    // Are we already connected?
    if (is_resource($dbLink) && mysql_ping($dbLink)) {
        return true;
    }
    return false;
}

// Keeps the database link up and running
function db_keepalive() {
    global $dbLink;

    if (db_isalive()) {
        return false;
    }
    while (!db_isalive()) {
        if (is_resource($dbLink)) {
            mysql_close($dbLink);
        }
        $dbLink = null;
        db_connect();
        // Wait for 1 second
        sleep(1);
    }
    return true;
}

// Escapes a string to be safely included in a query.
function db_escape($str) {
    return mysql_real_escape_string($str);
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
function db_query($query, $unbuffered = false) {
    global $dbLink;

    // Make sure we are connected.
    if (IA_DB_KEEP_ALIVE) {
        db_keepalive();
    }

    // Disable unbuffered queries.
    if (!IA_DB_MYSQL_UNBUFFERED_QUERY) {
        $unbuffered = false;
    }

    // Do the query.
    if ($unbuffered) {
        $result = mysql_unbuffered_query($query, $dbLink);
    } else {
        $result = mysql_query($query, $dbLink);
    }

    if (!$result) {
        // Query falied. Have we lost connection?
        if (IA_DB_KEEP_ALIVE && db_keepalive()) {
            // Try query again
            return db_query($query, $unbuffered);
        }
        log_print("Query: '$query'");
        log_error("MYSQL error: ".mysql_error($dbLink));
    } else {
        // Print query info.
        //log_backtrace();
        if (IA_LOG_SQL_QUERY && strpos($query, 'EXPLAIN') !== 0) {
            log_print("SQL QUERY: '$query'");
            if (!$unbuffered && strpos($query, 'SELECT') === 0) {
                log_print("SQL QUERY ROWS: ".db_num_rows($result));
            }
        }
        if (IA_LOG_SQL_QUERY_EXPLAIN && !$unbuffered &&
                strpos($query, 'SELECT') === 0) {
            // FIXME: pipes, proper format.
            $explanation = db_fetch_all("EXPLAIN EXTENDED $query");
            log_print("EXPLANATION:");
            if (count($explanation) > 0) {
                log_print('EXP: '.implode("\t", array_keys($explanation[0])));
                foreach ($explanation as $exprow) {
                    log_print('EXP: '.implode("\t", array_values($exprow)));
                }
            }
        }
    }

    if (IA_DEVELOPMENT_MODE) {
        global $execution_stats;
        $execution_stats['queries']++;
    }

    return $result;
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

?>
