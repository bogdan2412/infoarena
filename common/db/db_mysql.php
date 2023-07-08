<?php

// Connects to the database. Call this function if you need the database.
// It's better than connecting when the file is included. Side-effects are bad.
// Note: the wrapper around mysqli causes mysql_error() to barf when there is
// no connection.
function db_connect() {
    global $dbLink;
    // Repetitive include guard. Is this really needed?
    if (db_isalive()) {
        log_warn("Already connected to the database.");
        return true;
    }

    // log_print("Connecting to database...");
    if (!$dbLink = @mysql_connect(Config::DB_HOST, Config::DB_USER, Config::DB_PASSWORD)) {
        if (Config::DB_KEEP_ALIVE) {
            $timeout = 0;
            do {
                log_warn("Cannot connect to database, retrying in {$timeout} seconds.");

                // Wait for an increasing amount of seconds to avoid
                // strain on the mysql server if it is under heavy load
                sleep($timeout);
                $timeout = min(max(1, $timeout * 2), 60);

                // Try and reconnect to the database server
                $dbLink = @mysql_connect(Config::DB_HOST, Config::DB_USER, Config::DB_PASSWORD);
            } while (!db_isalive());
            log_print('Connected to database.');
        } else {
            log_error('Cannot connect to database.');
        }
    }
    if (!mysql_select_db(Config::DB_NAME, $dbLink)) {
        log_error('Cannot select database.');
    }
    mysql_query('SET NAMES utf8');
    return true;
}

function db_isalive() {
    global $dbLink;

    // Are we already connected?
    if (is_sql_resource($dbLink) && @mysql_ping($dbLink)) {
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
    if (is_sql_resource($dbLink)) {
        mysql_close($dbLink);
    }

    return db_connect();
}

// Escapes a string to be safely included in a query.
function db_escape($str, $check_null = false) {
    if ($check_null && is_null($str)) {
        return 'NULL';
    }

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
    if (Config::DB_KEEP_ALIVE) {
        db_keepalive();
    }

    // Disable unbuffered queries.
    if (!Config::DB_UNBUFFERED) {
        $unbuffered = false;
    }

    // Do the query.
    if ($unbuffered) {
        $result = @mysql_unbuffered_query($query, $dbLink);
    } else {
        $result = @mysql_query($query, $dbLink);
    }

    if (!$result) {
        // Query failed. Have we lost connection?
        if (Config::DB_KEEP_ALIVE && db_keepalive()) {
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

    if (Config::DEVELOPMENT_MODE) {
        global $execution_stats;
        $execution_stats['queries']++;
    }

    return $result;
}

// Frees mysql result
function db_free($result) {
    log_assert(is_sql_resource($result));
    mysql_free_result($result);
}

// Fetches next result row
function db_next_row($result) {
    return mysql_fetch_assoc($result);
}

function db_execute_sql_file(string $fileName): void {
    $command = sprintf('mysql -h %s -u %s %s %s < %s',
                       Config::DB_HOST,
                       Config::DB_USER,
                       Config::DB_NAME,
                       Config::DB_PASSWORD ? ('-p' . Config::DB_PASSWORD) : '',
                       $fileName);

    // TODO: better include system.
    require_once __DIR__ . '/../os.php';
    OS::executeAndAssert($command);
}
