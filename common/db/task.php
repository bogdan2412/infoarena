<?php

require_once Config::ROOT.'common/db/db.php';
require_once Config::ROOT.'common/task.php';
require_once Config::ROOT.'common/db/parameter.php';
require_once Config::ROOT.'common/db/round.php';
require_once Config::ROOT.'common/db/round_task.php';

// Get task by id. No params.
function task_get($task_id) {
  // this assert brakes templates pages with round_id = %round_id%
  log_assert(is_task_id($task_id));

  $query = sprintf("SELECT * FROM ia_task WHERE `id` = '%s'",
                   db_escape($task_id));

  return db_fetch($query);
}

// Create new task
function task_create($task, $task_params, $remote_ip_info = '') {
  log_assert_valid(task_validate($task));
  log_assert_valid(task_validate_parameters($task['type'], $task_params));

  $res = db_insert('ia_task', $task);
  if ($res) {
    // Insert parameters.
    task_update_parameters($task['id'], $task_params);

    // Copy templates.
    require_once Config::ROOT.'common/textblock.php';
    $replace = array('task_id' => $task['id'],
                     'task_title' => ucfirst($task['id']),
    );
    textblock_copy_replace('template/newtask', $task['page_name'],
                           $replace, "task: {$task['id']}",
                           $task['user_id'], $remote_ip_info);
  }
  return $res;
}

// Deletes a task from ia_round_task
function task_delete_from_rounds($task_id) {
  // Get all rounds for the task
  $query = 'SELECT DISTINCT round_id FROM ia_round_task
              WHERE task_id = '.db_quote($task_id);
  $res = db_fetch_all($query);

  // Delete task
  db_query('DELETE FROM ia_round_task WHERE task_id = '.db_quote($task_id));

  // Repair rounds order
  foreach ($res as $row) {
    round_task_recompute_order($row['round_id']);
  }
}

// Delete a task, including tags, scores, jobs and page
// WARNING: This is irreversible.
function task_delete($task) {
  log_assert_valid(task_validate($task));

  // Delete problem page
  textblock_delete($task['page_name']);

  // Delete all scores received on task
  db_query('DELETE FROM `ia_score_user_round_task`
              WHERE `task_id` = '.db_quote($task['id']));

  // Recompute round scores
  $query = 'SELECT `round_id` FROM `ia_round_task`
                WHERE `task_id` = '.db_quote($task['id']);
  $rounds = db_fetch_all($query);

  foreach ($rounds as $round) {
    round_recompute_score($round['round_id']);
  }

  // Remove task from all rounds
  task_delete_from_rounds($task['id']);

  // Delete task jobs
  $job_ids_fetched = db_fetch_all('
        SELECT `id`
        FROM `ia_job`
        WHERE `task_id` = '.db_quote($task['id']));

  $job_ids = array();
  foreach ($job_ids_fetched as $job) {
    $job_ids[] = (int)$job['id'];
  }

  if (count($job_ids)) {
    $formated_job_ids = implode(', ', array_map('db_quote', $job_ids));
    db_query("DELETE FROM `ia_job_test`
                  WHERE `job_id` IN ({$formated_job_ids})");
    db_query("DELETE FROM `ia_job`
                  WHERE `id` IN ({$formated_job_ids})");
  }

  // Delete task tags
  $query = sprintf('DELETE FROM ia_task_tags ' .
                   'WHERE task_id = "%s"',
                   $task['id']);
  db_query($query);

  // Delete task
  db_query("DELETE FROM `ia_task` WHERE `id` = '".
           db_escape($task['id'])."'");

  // Delete all task parameters
  task_update_parameters($task['id'], array());
}

function task_update($task) {
  log_assert_valid(task_validate($task));
  db_update('ia_task', $task,
            "`id` = '".db_escape($task['id'])."'");
}

// binding for parameter_get_values
function task_get_parameters($task_id) {
  log_assert(is_task_id($task_id));
  return parameter_get_values('task', $task_id);
}

// binding for parameter_update_values
function task_update_parameters($task_id, $param_values) {
  log_assert(is_task_id($task_id));
  parameter_update_values('task', $task_id, $param_values);
}

function task_get_all() {
  return db_fetch_all('SELECT * FROM ia_task order by id');
}

// Returns list of round ids that include this task
function task_get_parent_rounds($task_id) {
  log_assert(is_task_id($task_id));

  $query = sprintf('
        SELECT DISTINCT round_id
        FROM ia_round_task
        WHERE task_id=%s
        ORDER BY round_id
    ', db_quote($task_id));

  $rows = db_fetch_all($query);

  // transform rows into id list
  $idlist = array();
  foreach ($rows as $row) {
    $idlist[] = $row['round_id'];
  }

  return $idlist;
}

// Returns list of running round ids that include this task to which
// $user_id can submit
function task_get_submit_rounds($task_id) {
  $rounds = task_get_parent_rounds($task_id);
  foreach ($rounds as $id => $round) {
    $round = round_get($rounds[$id]);
    if ($round['state'] != 'running') {
      unset($rounds[$id]);
    }
  }
  return array_values($rounds);
}

function task_get_authors($task_id) {
  log_assert(is_task_id($task_id), 'Invalid task_id');

  return tag_get('task', $task_id, 'author');
}

// Task filter
// Returns only tasks that contain all the tags
// and are public
function task_filter_by_tags($tag_ids, $scores = true, $user_id = null) {
  log_assert(is_array($tag_ids), 'tag_ids must be an array');
  foreach ($tag_ids as $tag_id) {
    log_assert(is_tag_id($tag_id), 'invalid tag id');
  }

  if (count($tag_ids) > 0) {
    $tag_filter = 'AND '.tag_build_where('task', $tag_ids);
  } else {
    $tag_filter = '';
  }

  if ($user_id == null || $scores == false) {
    $join_score = '';
    $score_fields = '';
  } else {
    // we get only the biggest score, round doesn't matter
    $join_score = 'LEFT JOIN ia_score_user_round_task AS score ON
                            score.`user_id` = '.db_quote($user_id).' AND
                            score.`task_id` = ia_task.`id`';
    $score_fields = ',MAX(score.`score`) AS `score`';
  }


  // MariaDB is happy with just "GROUP BY ia_task.id", but MySQL wants all
  // the fields from SELECT listed in GROUP BY.
  $query = "SELECT ia_task.id AS task_id,
                ia_task.title AS task_title,
                ia_task.page_name AS page_name,
                ia_task.open_source AS open_source,
                ia_task.open_tests AS open_tests,
                ia_task.rating AS rating,
                ia_task.source AS source,
                ia_task.solved_by AS solved_by
                $score_fields
    FROM ia_task
    $join_score
    WHERE ia_task.security = 'public'
    $tag_filter
    GROUP BY ia_task.id, ia_task.title, ia_task.page_name, ia_task.open_source,
    ia_task.open_tests, ia_task.rating, ia_task.source, ia_task.solved_by
    ORDER BY task_title";

  $tasks = db_fetch_all($query);

  return $tasks;
}

// Returns the score a user got on a task in the archive.
// Optionally, a different round parameter can be specified.
function task_get_user_score($task_id, $user_id, $round_id = 'arhiva') {
  // Validate
  if (!is_round_id($round_id)) {
    log_error('Invalid round id');
  }
  if (!is_task_id($task_id)) {
    log_error('Invalid task id');
  }
  if (!is_user_id($user_id)) {
    log_error('Invalid user id');
  }

  // Query database
  $query = sprintf(
    'SELECT `score` FROM ia_score_user_round_task
         WHERE round_id = %s AND task_id = %s AND user_id = %s',
    db_quote($round_id), db_quote($task_id), db_quote($user_id));
  $res = db_fetch($query);
  $score = getattr($res, 'score', null);

  return $score;
}

/**
 * Returns the number of previous submits of an users to a task in a contest
 *
 * @param int $user_id
 * @param string $round_id
 * @param string $task_id
 * @return int
 */
function task_user_get_submit_count($user_id, $round_id, $task_id) {
  if (is_null($round_id) || $round_id === '') {
    // No round id means that the task is still being added by its author.
    return 0;
  }
  log_assert(is_user_id($user_id));
  log_assert(is_round_id($round_id));
  log_assert(is_task_id($task_id));

  $query = sprintf('SELECT `submits` FROM ia_score_user_round_task
            WHERE `user_id` = %s AND `round_id` = %s AND `task_id` = %s',
                   db_quote($user_id), db_quote($round_id), db_quote($task_id));
  $res = db_fetch($query);

  return getattr($res, 'submits', 0);
}

/**
 * Increment the submission counter for a specific user, round and task
 *
 * @param int $user_id
 * @param string $round_id
 * @param string $task_id
 * @parm int $submission
 */
function task_user_update_submit_count($user_id, $round_id, $task_id) {
  if (is_null($round_id) || $round_id === '') {
    // No round id means that the task is still being added by its author.
    return;
  }
  log_assert(is_user_id($user_id));
  log_assert(is_round_id($round_id));
  log_assert(is_task_id($task_id));

  $query = sprintf('INSERT INTO `ia_score_user_round_task` VALUES (%s,%s,%s'
                   .', 0, 1, 0) ON DUPLICATE KEY UPDATE `submits` = `submits` + 1',
                   db_quote($user_id), db_quote($round_id), db_quote($task_id));
  db_query($query);
}

/**
 * Updates the task security (default checks if the target is in an archive
 * if so it makes the task public, otherwise protected)
 * @param string $task_id
 * @param string $security
 */
function task_update_security($task_id, $security = 'check') {
  log_assert(is_task_id($task_id));
  log_assert(array_key_exists($security,
                              array_merge(task_get_security_types(),
                                          array('check' => null))));

  if ($security == 'check') {
    $security = task_in_archive_rounds($task_id) ? 'public' : 'protected';
  }

  $new_task = task_get($task_id);
  $new_task['security'] = $security;
  task_update($new_task);
}

/**
 * Checks whether the current task is in an archive
 *
 * @param string $task_id
 * @return bool
 */
function task_in_archive_rounds ($task_id) {
  log_assert(is_task_id($task_id));
  $parent_rounds = task_get_parent_rounds($task_id);
  foreach ($parent_rounds as $round_id) {
    $round = round_get($round_id);
    if ($round['type'] == 'archive') {
      return true;
    }
  }
  return false;
}

/**
 * Returns the round_id of the archive which includes this task
 * (should be unique)
 * If the task isn't in any archive, return an empty string.
 *
 * @param string $task_id
 * @return string
 */
function task_get_archive_round($task_id) {
  log_assert(is_task_id($task_id));
  $parent_rounds = task_get_parent_rounds($task_id);
  foreach ($parent_rounds as $round_id) {
    $round = round_get($round_id);
    if ($round['type'] == 'archive') {
      return $round['id'];
    }
  }
  return '';
}
