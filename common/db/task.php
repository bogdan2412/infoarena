<?php

require_once IA_ROOT_DIR.'common/db/db.php';
require_once IA_ROOT_DIR.'common/task.php';
require_once IA_ROOT_DIR.'common/db/parameter.php';
require_once IA_ROOT_DIR.'common/db/round_task.php';

// Add $task to cache if not null, return $task.
function _task_cache_add($task) {
    if (!is_null($task)) {
        log_assert_valid(task_validate($task));
        mem_cache_set("task-by-id:{$task['id']}", $task,
                      IA_MEM_CACHE_TASK_EXPIRATION);
    }
    return $task;
}

function _task_cache_delete($task) {
    mem_cache_delete("task-by-id:{$task['id']}");
}

// Get task by id. No params.
function task_get($task_id) {
    // this assert brakes templates pages with round_id = %round_id%
    log_assert(is_task_id($task_id));

    if (($res = mem_cache_get("task-by-id:$task_id")) !== false) {
        return $res;
    }

    $query = sprintf("SELECT * FROM ia_task WHERE `id` = '%s'",
                     db_escape($task_id));

    // This way nulls (missing tasks) get cached too.
    return mem_cache_set("task-by-id:$task_id", db_fetch($query),
                         IA_MEM_CACHE_TASK_EXPIRATION);
}

// Create new task
function task_create($task, $task_params, $remote_ip_info = null) {
    log_assert_valid(task_validate($task));
    log_assert_valid(task_validate_parameters($task['type'], $task_params));

    $res = db_insert('ia_task', $task);
    if ($res) {
        // Insert parameters.
        task_update_parameters($task['id'], $task_params);

        // Copy templates.
        require_once IA_ROOT_DIR.'common/textblock.php';
        $replace = array('task_id' => $task['id'],
                         'task_title' => ucfirst($task['id']),
        );
        textblock_copy_replace('template/newtask', $task['page_name'],
                               $replace, "task: {$task['id']}",
                               $task['user_id'], $remote_ip_info);

        _task_cache_add($task);
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

    // Delete task from cache
    _task_cache_delete($task);

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
    if (db_update('ia_task', $task,
            "`id` = '".db_escape($task['id'])."'")) {
        _task_cache_add($task);
    } else {
        _task_cache_delete($task);
    }
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

// Get all tasks.
function task_get_all() {
    $res = db_fetch_all('SELECT * FROM ia_task');
    foreach ($res as $task) {
        _task_cache_add($task);
    }
    return $res;
}

// Returns list of round ids that include this task
function task_get_parent_rounds($task_id, $force_no_cache = false) {
    log_assert(is_task_id($task_id));
    if (!$force_no_cache) {
        $result = mem_cache_get("task-rounds-by-id:$task_id");
        if ($result !== false) {
            return $result;
        }
    }

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

    mem_cache_set("task-rounds-by-id:$task_id", $idlist);
    return $idlist;
}

// Returns list of running round ids that include this task to which
// $user_id can submit
function task_get_submit_rounds($task_id, $user_id) {
    $rounds = task_get_parent_rounds($task_id);
    foreach ($rounds as $id => $round) {
        $round = round_get($rounds[$id]);
        if (!security_query($user_id, 'round-submit', $round)) {
            unset($rounds[$id]);
        }
    }
    return array_values($rounds);
}

function task_get_authors($task_id, $no_cache = false) {
    log_assert(is_task_id($task_id), 'Invalid task_id');

    $authors = false;
    if (!$no_cache) {
        $authors = mem_cache_get('task-authors-by-id:'.$task_id);
    }

    if ($authors === false) {
        $authors = tag_get('task', $task_id, 'author');
        mem_cache_set('task-authors-by-id:'.$task_id, $authors);
    }

    return $authors;
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

// Updates the forum topic associated with a task.
function task_update_forum_topic($task_id, $round_id = 'arhiva') {
    if (!is_task_id($task_id)) {
        log_error('Invalid task id');
    }

    // Get task info
    $query = 'SELECT title, page_name FROM ia_task
              WHERE id = '.db_quote($task_id);
    $task = db_fetch($query);

    // Get the forum topic
    $query = 'SELECT forum_topic
              FROM ia_textblock
              WHERE name = '.db_quote($task['page_name']);
    $res = db_fetch($query);
    $topic_id = $res['forum_topic'];

    // Check the textblock has an associated forum topic.
    if (is_null($topic_id)) {
        return;
    }

    // Get the first message from the topic
    $query = 'SELECT ID_FIRST_MSG AS `msg_id`
              FROM ia_smf_topics
              WHERE ID_TOPIC = '.db_quote($topic_id);
    $res = db_fetch($query);
    // Topic id doesn't exist
    if (is_null($res)) {
        return;
    }
    $message_id = $res['msg_id'];

    // Get the subject and the body of the message
    $query = 'SELECT subject, body FROM ia_smf_messages
              WHERE ID_MSG = '.db_quote($message_id);
    $message = db_fetch($query);

    // Find the number associated with the (task, round) pair.
    $query = sprintf(
        'SELECT order_id FROM ia_round_task
         WHERE round_id = %s AND task_id = %s',
         db_quote($round_id), db_quote($task_id));
    $res = db_fetch($query);
    $task_number = sprintf('%03d', $res['order_id'] - 1);

    // New info
    $new_subject = $task_number.' '.$task['title'];
    $body_start = mb_substr($message['body'], 0, 35);
    if ($body_start != 'Aici puteti discuta despre problema' &&
        $body_start != 'Aici puteÈ›i discuta despre prob' &&
        $body_start != 'Aici puteÅ£i discuta despre probl') {
        $new_body = 'Aici puteÅ£i discuta despre problema '.
                    '[url=http://infoarena.ro/problema/'.$task_id.']'.
                    $task['title'].'[/url].';
    } else {
        $new_body = $message['body'];
    }

    // Finally, update the message
    $query = sprintf(
        'UPDATE ia_smf_messages
         SET subject = %s, body = %s
         WHERE ID_MSG = %s',
         db_quote($new_subject), db_quote($new_body), db_quote($message_id));
    db_query($query);

    // Not finished yet, must change replies too.
    // This is extremely time consuming.
    if ($new_subject != $message['subject']) {
        $query = sprintf(
            'UPDATE ia_smf_messages
             SET subject = %s
             WHERE subject LIKE %s
               AND ID_MSG <> %s',
             db_quote('RÄƒspuns: '.$new_subject),
             db_quote('%'.$message['subject']),
             db_quote($message_id));
        db_query($query);
    }
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

    // Check the cache
    $cache_key = 'user-task-round:'.$user_id.'-'.$task_id.'-'.$round_id;
    if (($res = mem_cache_get($cache_key)) != false) {
        return $res;
    }

    // Query database
    $query = sprintf(
        'SELECT `score` FROM ia_score_user_round_task
         WHERE round_id = %s AND task_id = %s AND user_id = %s',
         db_quote($round_id), db_quote($task_id), db_quote($user_id));
    $res = db_fetch($query);
    $score = getattr($res, 'score', null);

    // Keep in cache
    mem_cache_set($cache_key, (int)$score);

    return $score;
}

/**
 * Returns the maximum last score a user got on a task in archives.
 *
 * @param $task_id string
 * @param $user_id string
 * @return int
*/
function task_get_user_last_score($task_id, $user_id) {
    // Validate
    log_assert(is_task_id($task_id));
    log_assert(is_user_id($user_id));

    // Check the cache
    $cache_key = 'user-task-last-score:'.$user_id.'-'.$task_id;
    if (($res = mem_cache_get($cache_key)) != false) {
        return $res;
    }

    // Query database
    $query = sprintf(
        "SELECT MAX(`score`) as maxscore FROM ia_score_user_round_task
         LEFT JOIN ia_round ON ia_round.id = round_id AND
         ia_round.type = 'archive' WHERE task_id = %s AND
         ia_score_user_round_task.user_id = %s ",
         db_quote($task_id), db_quote($user_id));
    $res = db_fetch($query);
    $score = getattr($res, 'maxscore', null);

    // Keep in cache
    mem_cache_set($cache_key, (int)$score);

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

/**
 * Returns whether or not a user has solved a task.
 * If the user_id is null (like for example, an anonymous user)
 * this function always returns false.
 *
 * @param string task_id
 * @param int user_id
 * @return bool
 */
function task_user_has_solved($task_id, $user_id) {
    log_assert(is_task_id($task_id));
    if ($user_id === null) {
        return false;
    }
    log_assert(is_user_id($user_id));

    $query = sprintf(
        'SELECT score as maxscore FROM ia_score_user_round_task
         WHERE task_id = %s AND user_id = %s AND score =
            (SELECT MAX(`score`) FROM ia_score_user_round_task
             WHERE task_id = %s)
         LIMIT 1',
        db_quote($task_id),
        db_quote($user_id),
        db_quote($task_id));
    $res = db_fetch($query);
    if ($res === null) {
        return false;
    }
    return true;
}

/**
 * Returns whether or not the user has force viewed a source for this task
 * If the user_id is null (like for example, an anonymous user)
 * this function always returns false.
 *
 * @param string task_id
 * @param int user_id
 * @return bool
 */
function task_has_force_viewed_source($task_id, $user_id) {
    log_assert(is_task_id($task_id));
    if ($user_id === null) {
        return false;
    }

    log_assert(is_user_id($user_id));

    $query = sprintf(
        'SELECT * FROM ia_task_view_sources
         WHERE task_id = %s AND user_id = %s',
        db_quote($task_id),
        db_quote($user_id));
    $res = db_fetch($query);
    if ($res === null) {
        return false;
    }
    return true;
}

/**
 * Updates the ia_task_view_sources table with information that
 * a certain user has viewed a source of a task he has not solved yet
 *
 * @param string task_id
 * @param int user_id
 * @return void
 */
function task_force_view_source($task_id, $user_id) {
    log_assert(is_task_id($task_id));
    if ($user_id === null) {
        return;
    }
    log_assert(is_user_id($user_id));
    $query = sprintf(
        'INSERT IGNORE INTO ia_task_view_sources
        VALUES (%s, %s, %s)',
        db_quote($user_id),
        db_quote($task_id),
        db_quote(db_date_format()));
    db_query($query);
}
