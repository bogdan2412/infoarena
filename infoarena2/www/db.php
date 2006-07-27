<?php
/**
 * This module contains various database-related functions and routines.
 *
 * Note: We keep database-persisted "models" very simple. Most of them are
 * simple dictionaries. 
 */

// first, we need a database connection
assert(!isset($dbLink));    // repetitive-include guard
$dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS)
    or die('Cannot connect to database.');
    mysql_select_db(DB_NAME, $dbLink) or die ('Cannot select database.');


    // Escapes a string to be safely included in a query.
    function db_escape($str) {
        return mysql_escape_string($str);
    }

// Executes query, fetches only FIRST result
function db_fetch($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
    if ($result) {
        $row = mysql_fetch_assoc($result);
        return $row;
    }
    else {
        return null;
    }
}

// Executes query, fetches the whole result
function db_fetch_all($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
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


/**
 * Task
 */
function task_get($id) {
    $query = sprintf("SELECT * FROM ia_task WHERE id = '%s'", db_escape($id));
    return db_fetch($query);
}

/**
 * Wiki
 */

// Gets the latest version of a page, or null if the page is missing.
function wikipage_get($name) {
    $query = sprintf("SELECT * FROM ia_page ".
                     "WHERE LCASE(`name`) = LCASE('%s') ".
                     "ORDER BY `timestamp` DESC LIMIT 1",
                      db_escape($name));
    return db_fetch($query);
}

// Do use later.
function wikipage_add_revision($name, $content, $user) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_page (name, `text`, timestamp) ".
                     "VALUES ('%s', '%s', NOW())",
                     db_escape($name), db_escape($content));
    return mysql_query($query, $dbLink);
}

/**
 * User
 */
function user_get_by_username($username) {
    $query = sprintf("SELECT * FROM ia_user WHERE username = '%s'",
            db_escape($username));
    return db_fetch($query);
}

function user_get_by_id($id) {
    $query = sprintf("SELECT * FROM ia_user WHERE id = '%s'",
            db_escape($id));
    return db_fetch($query);
}

?>
