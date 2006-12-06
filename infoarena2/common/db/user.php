<?php

require_once(IA_ROOT."common/db/db.php");

/**
 * User-related functions.
 */

// Password hash function. Must be compatible with SMF.
//
// Also takes into account user name so that users
// sharing the same password can't be detected
function user_hash_password($password, $username) {
    return sha1(strtolower($username).$password);
}

// Test password in IA1 format.
function user_test_ia1_password($username, $password) {
    // old ia1 users are expected to have the ia1 hashed password
    // as their actual password
    $password = db_query_value(sprintf("SELECT PASSWORD('%s')",
                                       db_escape($password)));
    // hash password ia2 style
    $password = user_hash_password($password, $username);
    // test
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = LCASE('%s') AND
                            '%s' = `password`",
                     db_escape($username), db_escape($password));
    return db_fetch($query);
}

// Check user's password
function user_test_password($username, $password) {
    // hash password
    $password = user_hash_password($password, $username);
    // test
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = LCASE('%s')
                            AND '%s' = `password`",
                     db_escape($username),
                     db_escape($password));
    return db_fetch($query);
}

// Get user information.
function user_get_by_username($username) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = LCASE('%s')",
                     db_escape($username));
    return db_fetch($query);
}

function user_get_by_email($email) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(email) = LCASE('%s')",
                     db_escape($email));
    return db_fetch($query);
}

function user_get_by_id($id) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE id = '%s'",
                     db_escape($id));
    return db_fetch($query);
}

// Create a new user.
// FIXME: Expand $data into specific fields just like task_create ()!
function user_create($data) {
    $query = "INSERT INTO ia_user (";
    foreach ($data as $key => $val) {
        $query .= '`' . $key . '`,';
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ') VALUES (';
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $val = user_hash_password($val, $data['username']);
        }

        $query .= "'" . db_escape($val) . "',";
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= ')';

    // create user
    log_print('Creating database entry for user: '.$data['username']);
    db_query($query);
    $new_user = user_get_by_username($data['username']);
    log_assert($new_user, 'Registration input data was validated OK but no database entry was created');

    require_once(IA_ROOT . "common/textblock.php");
    $replace = array("user_id" => $data['username']);
    textblock_copy_replace("template/newuser", TB_USER_PREFIX.$data['username'],
                           $replace, "public", $new_user['id']);

    return $new_user;
}

// Update user information.
// NOTE: When updating password, it is mandatory that you also specify username
// FIXME: Expand $data into specific fields just like task_create ()!
function user_update($data, $id) {
    $query = "UPDATE ia_user SET ";
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $val = user_hash_password($val, $data['username']);
        }
        $query .= "`" . $key . "`='" . db_escape($val) . "',";
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= " WHERE `id` = '" . db_escape($id) . "'";

    return db_query($query);
}

// Returns array with *all* registered usernames.
// Please use this wisely.
function user_get_list() {
    $rows = db_fetch_all("SELECT * FROM ia_user");
    $users = array();
    foreach ($rows as $row) {
        $users[] = $row['username'];
    }
    return $users;
}

// Counts number of uers
function user_count() {
    $result = db_fetch("SELECT COUNT(*) FROM ia_user");
    return $result["COUNT(*)"];
}

?>
