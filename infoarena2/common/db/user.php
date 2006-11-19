<?php

require_once("db.php");

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

    // create associated textblock entry
    // default (initial) content is taken from an existing template
    $template = textblock_get_revision('template/newuser');
    log_assert($template, 'Could not find template for new user: template/newuser');
    $title = str_replace('%user_id%', $new_user['username'], $template['title']);
    $content = str_replace('%user_id%', $new_user['username'], $template['text']);
    textblock_add_revision('user/'.$new_user['username'], $title, $content,
                           $new_user['id'], $security = '');

    return $new_user;
}

// Update user information.
// NOTE: When updating password, it is mandatory that you also specify username
function user_update($data, $id) {
    global $dbLink;
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

?>
