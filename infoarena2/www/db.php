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

// Executes query. Outputs error messages
// Returns native PHP mysql resource handle
function db_query($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
    if (!$result) {
        // An error has occured. Print helpful debug messages and die
        echo '<br/><br/><hr/><h1>SQL ERROR!</h1>';

        if (IA_SQL_TRACE) {
            echo '<p>' . mysql_error($dbLink) . '</p>';
            echo '<p>This has occured upon trying to execute this:</p>';
            echo '<pre>' . $query . '</pre>';
        }
        die();
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

/**
 * Task
 */
function task_get($id) {
    $query = sprintf("SELECT *
                      FROM ia_task
                      WHERE id = '%s'",
                    db_escape($id));
    return db_fetch($query);
}

/**
 * Textblocks and textblock revisions
 */
// Call this function to add a new revision.
function textblock_add_revision($name, $title, $content, $user_id) {
    global $dbLink;

    // copy current version to revision table
    $query = sprintf("INSERT INTO ia_textblock_revision
                        SELECT *
                        FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'",
                    db_escape($name));
    db_query($query);
    // replace current version
    $query = sprintf("DELETE FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'
                      LIMIT 1",
                    db_escape($name));
    db_query($query);
    $query = sprintf("INSERT INTO ia_textblock
                        (name, `text`, `title`, `timestamp`, user_id)
                      VALUES ('%s', '%s', '%s', NOW(), '%s')",
                     db_escape($name), db_escape($content),
                     db_escape($title), db_escape($user_id));
    return db_query($query);
}

function textblock_get_revision($name, $revNumber = null) {
    global $dbLink;
    if (is_null($revNumber)) {
        $query = sprintf("SELECT *
                          FROM ia_textblock
                          WHERE LCASE(`name`) = '%s'",
                        db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
    }
    else {
        $query = sprintf("SELECT *
                          FROM ia_textblock_revision
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($revNumber));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

function textblock_get_revision_count($name) {
    global $dbLink;
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock_revision
                      WHERE LCASE(`name`) = '%s'",
                    db_escape($name));
    $row = db_fetch($query);
    return $row['cnt'];
}

function textblock_get_names($prefix) {
   $query = sprintf("SELECT `name`, title, user_id, `timestamp`
                     FROM ia_textblock
                     WHERE LCASE(`name`) LIKE '%s%%'
                     ORDER BY `name`",
                   db_escape($prefix));
    return db_fetch_all($query);
}

function textblock_get_names_with_user($prefix) {
    $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                      FROM ia_textblock
                        LEFT JOIN ia_user ON ia_textblock.user_id = ia_user.id
                      WHERE LCASE(`name`) LIKE '%s%%'
                      ORDER BY `name`",
                    db_escape($prefix));
    return db_fetch_all($query);
}

/**
 * User
 */
function user_test_password($username, $password) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s' AND SHA1('%s') = `password`",
                    db_escape($username), db_escape($password));
    return db_fetch($query);
}

function user_get_by_username($username) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s'",
                    db_escape($username));
    return db_fetch($query);
}

function user_get_by_email($email) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(email) = '%s'",
                    db_escape($email));
    return db_fetch($query);
}

function user_get_by_id($id) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE id = '%s'", db_escape($id));
    return db_fetch($query);
}

function user_create($data) {
    global $dbLink;
    $query = "INSERT INTO ia_user (";
    foreach ($data as $key => $val) {
        $query .= '`' . $key . '`,';
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ') VALUES (';
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $query .= "sha1('" . db_escape($val) . "'),";
        }
        else {
            $query .= "'" . db_escape($val) . "',";
        }
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= ')';

//    print $query; // debug info
    return db_query($query);
}

function user_update($data, $id)
{
    global $dbLink;
    $query = "UPDATE ia_user SET ";
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $query .= "`" . $key . "`=sha1('" . db_escape($val) . "'),";
        }
        else {
            $query .= "`" . $key . "`='" . db_escape($val) . "',";
        }
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= " WHERE `id` = '" . db_escape($id) . "'";

//    print $query; // debug info
    return db_query($query);
}

/**
 * Attachment
 */
function attachment_get($name, $page) {
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                    db_escape($name), db_escape($page));
    return db_fetch($query);
}

function attachment_get_by_id($id) {
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE `id` = '%s'",
                    db_escape($id));
    return db_fetch($query);
}

function attachment_update($name, $size, $page, $user_id) {
    $query = sprintf("UPDATE ia_file
                      SET size = '%s', user_id ='%s', `timestamp` = NOW()
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                    db_escape($size), db_escape($user_id),
                    db_escape($name), db_escape($page));
    db_query($query);
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                    db_escape($name), db_escape($page));
    $tmp = db_fetch($query);
    return $tmp['id'];
}

function attachment_insert($name, $size, $page, $user_id) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_file
                        (name, page, size, user_id, `timestamp`)
                      VALUES ('%s', '%s', '%s', '%s', NOW())",
                    db_escape($name), db_escape($page),
                    db_escape($size), db_escape($user_id));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function attachment_delete($name, $page) {
    global $dbLink;
    $query = sprintf("DELETE FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')
                      LIMIT 1",
                    db_escape($name), db_escape($page));
    return db_query($query);
}

function attachment_get_all($page) {
    $query = sprintf("SELECT *
                      FROM ia_file
                        LEFT JOIN ia_user ON ia_file.user_id = ia_user.id
                      WHERE LCASE(ia_file.page) = LCASE('%s')
                      ORDER BY ia_file.`timestamp` DESC",
                    db_escape($page));
    return db_fetch_all($query);
}

/**
 * News
 */
function news_get_range($start, $range) {
    $query = sprintf("SELECT *
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'news/%%'
                      ORDER BY `timestamp` DESC
                      LIMIT %s,%s",
                    $start, $range);
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
