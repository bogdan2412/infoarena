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
    log_print('Creating database entry for user: '.$data['username']);
    return db_query($query);
    $new_user = user_get_by_username($data['username']);
    log_assert($new_user, 'Registration input data was validated OK but no database entry was created');

    // create associated textblock entry
    // default (initial) content is taken from an existing template
    $title = "Profil utilizator '{$new_user['full_name']}' ({$new_user['username']})";
    $template = textblock_get_revision('template/newuser');
    log_assert($template, 'Could not find template for new user: template/newuser');
    $content = str_replace('%user_id%', $new_user['username'], $template['text']);
    $content = str_replace('%full_name%', $new_user['full_name'], $content);
    textblock_add_revision('user/'.$new_user['username'], $title, $content, $new_user['id']);

    return $new_user;
}

// Update user infos.
function user_update($data, $id) {
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


