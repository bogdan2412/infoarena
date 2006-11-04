<?php

require_once("db.php");

/**
 * User-related functions.
 */

// Test password in IA1 format.
function user_test_ia1_password($username, $password) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s' AND
                        SHA1(PASSWORD('%s')) = `password`",
                     db_escape($username), db_escape($password));
    return db_fetch($query);
}

// Check user's password
function user_test_password($username, $password) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s' AND
                        SHA1('%s') = `password`",
                     db_escape($username), db_escape($password));
    return db_fetch($query);
}

// Get user information.
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
                      WHERE id = '%s'",
                     db_escape($id));
    return db_fetch($query);
}

// Create a new user.
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
            $query .= "SHA1('" . db_escape($val) . "'),";
        }
        else {
            $query .= "'" . db_escape($val) . "',";
        }
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= ')';

    // create user
    log_print('Creating database entry for user: '.$data['id']);
    db_query($query);
}

// Update user infos.
function user_update($data, $id)
{
    global $dbLink;
    $query = "UPDATE ia_user SET ";
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $query .= "`" . $key . "`=SHA1('" . db_escape($val) . "'),";
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


