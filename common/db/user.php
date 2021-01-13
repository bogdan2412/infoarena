<?php

require_once(IA_ROOT_DIR."common/db/db.php");
require_once(IA_ROOT_DIR."common/db/smf.php");
require_once(IA_ROOT_DIR."common/user.php");
require_once(IA_ROOT_DIR."common/cache.php");
//require_once(IA_ROOT_DIR."common/db/tags.php");

// Add an user to the cache, or update if already there.
// Nothing happens if null is passed.
//
// Query functions can use this safely, it will just ignore nulls.
// Always returns $user.
function _user_cache_add($user) {
    if (!is_null($user)) {
        log_assert_valid(user_validate($user));
        mem_cache_set("user-by-name:{$user['username']}", $user);
        mem_cache_set("user-by-id:{$user['id']}", $user);
    }
    return $user;
}

// Delete an user from the cache. $user must be a valid user.
function _user_cache_delete($user) {
    log_assert_valid(user_validate($user));
    mem_cache_delete("user-by-name:{$user['username']}");
    mem_cache_delete("user-by-id:{$user['id']}");
}

// Test password in IA1 format.
function user_test_ia1_password($username, $password)
{
    // old ia1 users are expected to have the ia1 hashed password
    // as their actual password

    // The password() function was removed in MySQL 8.0. Use the workaround
    // described at https://stackoverflow.com/a/60243956/6022817
    $query = "select concat('*', upper(sha1(unhex(sha1('%s')))))";
    $password = db_query_value(sprintf($query, db_escape($password)));
    // hash password ia2 style
    $password = user_hash_password($password, $username);
    // test
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE username = '%s' AND '%s' = `password`",
                     db_escape($username), db_escape($password));

    return _user_cache_add(db_fetch($query));
}

// Check user's password.
// Returns user struct or null if user/passwrd is wrong.
function user_test_password($username, $password)
{
    // Query database
    $hash = user_hash_password($password, $username);
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE username = %s AND `password` = %s",
                     db_quote($username), db_quote($hash));
    $user = db_fetch($query);

    // Store in cache
    return _user_cache_add(db_fetch($query));
}

// Get user by email. Returns valid user or null.
function user_get_by_email($email)
{
    // Query database.
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE email = '%s'",
                     db_escape($email));
    $user = db_fetch($query);

    // Store in cache if not null.
    return _user_cache_add(db_fetch($query));
}

// Get user by username. Returns valid user or null.
function user_get_by_username($user_name)
{
    // Check the cache.
    if (($res = mem_cache_get("user-by-name:$user_name")) !== false) {
        return $res;
    }

    // Query database.
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE username = '%s'",
                     db_escape($user_name));
    $user = db_fetch($query);

    // Keep in cache.
    // If we didn't find an user we store a null in the cache by hand.
    if ($user !== null) {
        return _user_cache_add($user);
    } else {
        mem_cache_set("user-by-name:$user_name", null);
        return null;
    }
}

// Get an user struct by his numeric id.
// Remarkably similar to user_get_by_username.
function user_get_by_id($user_id)
{
    // Check the cache.
    if (($res = mem_cache_get("user-by-id:$user_id")) !== false) {
        return $res;
    }

    // Query database
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE id = %s",
                     db_quote($user_id));
    $user = db_fetch($query);

    // Keep in cache
    if ($user !== null) {
        return _user_cache_add($user);
    } else {
        mem_cache_set("user-by-id:$user_id", null);
        return null;
    }

}

// Create a new user.
// This also creates an user page and SMF user.
// $user must be a valid user struct, user_id ignored
// Returns created $user or throws up on error.
function user_create($user, $remote_ip_info=null)
{
    log_assert_valid(user_validate($user));

    // DB magic.
    unset($user['id']);
    db_insert("ia_user", $user);
    $user['id'] = db_insert_id();

    // Check db magic
    // FIXME: do we really need this?
    _user_cache_delete($user);
    $new_user = user_get_by_username($user['username']);
    log_assert($new_user, "Failed creating user");

    // Create user page.
    require_once(IA_ROOT_DIR . "common/textblock.php");
    $replace = array("user_id" => $user['username']);
    textblock_copy_replace('template/newuser',
                           IA_USER_TEXTBLOCK_PREFIX.$user['username'],
                           $replace, 'public',
                           $new_user['id'], $remote_ip_info);

    // Create SMF user
    require_once(IA_ROOT_DIR."common/db/smf.php");
    $smf_id = smf_create_user($user);
    log_assert($smf_id, "SMF user for {$user['username']} not created.");

    // Cache and return
    return _user_cache_add($new_user);
}

// Update user information.
// $user should be a complete user struct.
// Uses db_update evilness.
// Also updates SMF, which is good.
function user_update($user)
{
    log_assert_valid(user_validate($user));

    // Update DB
    db_update("ia_user", $user, "id = ".db_quote($user['id']));

    // Update SMF
    smf_update_user($user);

    _user_cache_add($user);
}

// Returns array with *all* registered usernames.
// FIXME: grep and exterminate.
function user_get_list($all_fields = false) {
    $rows = db_fetch_all("SELECT * FROM `ia_user`");
    $users = array();
    foreach ($rows as $row) {
        if ($all_fields) {
            $users[$row['username']] = $row;
        }
        else {
            $users[] = $row['username'];
        }
    }
    return $users;
}

// Get all users.
// Do not fucking use.
function user_get_all() {
    return db_fetch_all("SELECT * FROM `ia_user`");
}

// Counts number of users, cached.
function user_count() {
    if (($res = mem_cache_get("total-user-count")) !== false) {
        return $res;
    }
    $result = db_query_value("SELECT COUNT(*) FROM ia_user");
    return mem_cache_set("total-user-count", $result);
}

// Returns array with user submitted tasks. Filter tasks by choosing whether
// to select failed and solved tasks.
function user_submitted_tasks($user_id, $rounds,
                              $solved = true, $failed = true) {
    // construct where
    if ($solved && $failed) {
        // no condition
        $where = '';
    }
    elseif ($solved) {
        $where = 'AND ia_score_user_round_task.score = 100';
    }
    elseif ($failed) {
        $where = 'AND ia_score_user_round_task.score < 100';
    } else {
        // This shouldn't happen
        log_error('You can\'t select nothing.');
    }

    if ($rounds == null) {
        $rounds = array('');
    }

    $archives = db_escape_array($rounds);
    $query = sprintf("SELECT *
        FROM ia_score_user_round_task
        LEFT JOIN ia_task ON ia_task.id = ia_score_user_round_task.task_id
        WHERE ia_score_user_round_task.user_id = '%s'
        AND ia_score_user_round_task.round_id
            IN (%s)
        AND NOT ia_task.id IS NULL %s
        GROUP BY ia_task.id
        ORDER BY ia_task.`order`", $user_id, $archives, $where);

    return db_fetch_all($query);
}

// Returns array with rounds that user has submitted to tasks.
function user_submitted_rounds($user_id) {
    $query = "SELECT *
        FROM ia_score_user_round
        LEFT JOIN ia_round ON ia_round.id = ia_score_user_round.round_id
        WHERE ia_score_user_round.user_id = '%s'
              AND ia_round.type IN ('classic', 'penalty-round')";
    $query = sprintf($query, $user_id);

    return db_fetch_all($query);
}
