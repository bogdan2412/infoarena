<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/user.php");

/**
 * User-related functions.
 */

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
    //log_print('Creating database entry for user: '.$data['username']);
    db_query($query);
    $new_user = user_get_by_username($data['username']);
    log_assert($new_user, 'Registration input data was validated OK but no database entry was created');

    require_once(IA_ROOT_DIR . "common/textblock.php");
    $replace = array("user_id" => $data['username']);
    textblock_copy_replace("template/newuser", IA_USER_TEXTBLOCK_PREFIX.$data['username'],
                           $replace, "public", $new_user['id']);

    return $new_user;
}

// Update user information.
// NOTE: When updating password, it is mandatory that you also specify username
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
function user_get_list($all_fields = false) {
    $rows = db_fetch_all("SELECT * FROM ia_user");
    if ($all_fields) {
        return $rows;
    }
    $users = array();
    foreach ($rows as $row) {
        $users[] = $row['username'];
    }
    return $users;
}

// Counts number of uers
function user_count() {
    $result = db_query_value("SELECT COUNT(*) FROM ia_user");
    return $result;
}

// Returns array with user submitted tasks. Filter tasks by choosing whether
// to select failed and solved tasks.
function user_submitted_tasks($user_id, $solved = true, $failed = true) {
    // construct where
    if ($solved && $failed) {
        // no condition
        $where = '';
    }
    elseif ($solved) {
        $where = 'AND ia_score.score = 100';
    }
    elseif ($failed) {
        $where = 'AND ia_score.score < 100';
    }
    else {
        // This shouldn't happen
        log_error('You can\'t select nothing.');
    }

    $query = "
        SELECT *
        FROM ia_score
        LEFT JOIN ia_task ON ia_task.id = ia_score.task_id
        WHERE ia_score.`name` = 'score' AND ia_score.user_id = '%s'
              AND ia_score.round_id = 'arhiva' AND NOT ia_task.id IS NULL %s
        GROUP BY ia_task.id
        ORDER BY ia_task.`order`";
    $query = sprintf($query, $user_id, $where);

    return db_fetch_all($query);
}

// Returns array with rounds that user has submitted to tasks.
function user_submitted_rounds($user_id) {
    // FIXME: Find a way to remove the hard-coded "<> 'arhiva'"
    $query = "
        SELECT *
        FROM ia_score
        LEFT JOIN ia_round ON ia_round.id = ia_score.round_id
        WHERE ia_score.`name` = 'score' AND ia_score.user_id = '%s'
              AND NOT ia_round.id IS NULL AND ia_round.id <> 'arhiva'
        GROUP BY ia_round.id";
    $query = sprintf($query, $user_id);

    return db_fetch_all($query);
}

?>
