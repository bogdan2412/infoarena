<?php

require_once(Config::ROOT."common/db/db.php");
require_once(Config::ROOT."common/user.php");
//require_once(Config::ROOT."common/db/tags.php");

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
  return db_fetch($query);
}

// Get user by email. Returns valid user or null.
function user_get_by_email($email)
{
  // Query database.
  $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE email = '%s'",
                   db_escape($email));
  return db_fetch($query);
}

// Get user by username. Returns valid user or null.
function user_get_by_username($user_name)
{
  $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE username = '%s'",
                   db_escape($user_name));
  return db_fetch($query);
}

// Get an user struct by his numeric id.
// Remarkably similar to user_get_by_username.
function user_get_by_id($user_id)
{
  $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE id = %s",
                   db_quote($user_id));
  return db_fetch($query);
}

// Create a new user.
// This also creates a user page.
// $user must be a valid user struct, user_id ignored
// Returns created $user or throws up on error.
function user_create($user, $remote_ip_info = '')
{
  log_assert_valid(user_validate($user));

  // DB magic.
  unset($user['id']);
  db_insert("ia_user", $user);
  $user['id'] = db_insert_id();

  // Check db magic
  // FIXME: do we really need this?
  $new_user = user_get_by_username($user['username']);
  log_assert($new_user, "Failed creating user");

  // Create user page.
  require_once(Config::ROOT . "common/textblock.php");
  $replace = array("user_id" => $user['username']);
  textblock_copy_replace('template/newuser',
                         Config::USER_TEXTBLOCK_PREFIX.$user['username'],
                         $replace, 'public',
                         $new_user['id'], $remote_ip_info);

  return $new_user;
}

// Update user information.
// $user should be a complete user struct.
// Uses db_update evilness.
function user_update($user)
{
  log_assert_valid(user_validate($user));

  // Update DB
  db_update("ia_user", $user, "id = ".db_quote($user['id']));
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

function user_count() {
  return db_query_value("SELECT COUNT(*) FROM ia_user");
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
