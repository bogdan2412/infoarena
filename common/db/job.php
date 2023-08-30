<?php

require_once(Config::ROOT."common/db/db.php");

/*function db_format_field_list($fields) {
    return $result;
    foreach ($fields as $k => $v) {
        if ($result != '') {
            $result .= ', ';
        }
        if (is_array($v)) {
            $result .= "`{$v[0]}`.`{$v[1]}`";
        } else {
            $result .= "`{$v}`";
        }
        if (is_string($k)) {
            $result .= " AS `$k`";
        }
    }
    return $result;
}*/

// Creates new eval job
function job_create($task_id, $round_id, $user_id, $compiler_id, $file_contents,
        $remote_ip_info = '') {
    /**
     * Check which submission is the current one(first, second, ...)
     * Counting from 0
     */
    $submission = task_user_get_submit_count($user_id, $round_id, $task_id);
    $query = <<<SQL
        INSERT INTO ia_job
            (`task_id`, `round_id`, `user_id`, `compiler_id`, `file_contents`,
             `submit_time`, `remote_ip_info`, `submissions`)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
SQL;
    $query = sprintf($query,
            db_quote($task_id), db_quote($round_id), db_quote($user_id),
            db_quote($compiler_id), db_quote($file_contents),
            db_quote(db_date_format()), db_quote($remote_ip_info),
            db_quote($submission));

    /**
     * Increment the submission count
     */
    task_user_update_submit_count($user_id, $round_id, $task_id);
    return db_query($query);
}

// Get something for the evaluator to do.
// Null if nothing is found.
function job_get_next_job() {
    $query = <<<SQL
SELECT `job`.`id`, `job`.`user_id`, `job`.`task_id`, `job`.`round_id`,
       `job`.`compiler_id`, `job`.`status`, `job`.`submit_time`,
       `job`.`eval_message`, `job`.`score`, `job`.`file_contents`,
       `job`.`remote_ip_info`, `job`.`submissions`
    FROM `ia_job` AS `job`
    WHERE `job`.`id` = (
        SELECT MIN(id)
        FROM `ia_job`
        WHERE (`status` IN ('waiting', 'processing'))
    )
SQL;
    return db_fetch($query);
}

// Update job status.
// Null parameters doesn't update.
function job_update($job_id, $status = null,  $eval_message = null,
                    $eval_log = null, $score = null) {
    log_assert(is_whole_number($job_id));

    // Build set statements.
    $set_statements = array();
    if ($status !== null) {
        log_assert($status == 'processing' ||
                   $status == 'waiting' ||
                   $status == 'skipped' ||
                   $status == 'done', "Invalid status");
        $set_statements[] = "`status` = '".db_escape($status)."'";
    }
    if ($eval_message !== null) {
        $set_statements[] = "`eval_message` = '".db_escape($eval_message)."'";
    }
    if ($eval_log !== null) {
        $set_statements[] = "`eval_log` = '".db_escape($eval_log)."'";
    }
    if ($score !== null) {
        $set_statements[] = "`score` = '".db_escape($score)."'";
    }
    $query = sprintf("UPDATE ia_job SET %s WHERE id = %s",
            implode(', ', $set_statements), $job_id);
    db_query($query);
    return db_affected_rows();
}

function job_get_by_id($job_id, $contents = false) {
    log_assert(is_whole_number($job_id));
    $field_list = '`job`.`id`, job.`user_id`, `job`.`compiler_id`,
                   `job`.`status`, `job`.`submit_time`, `job`.`eval_message`,
                   `job`.`score`, `job`.`eval_log`, `job`.`remote_ip_info`,
                   `job`.`submissions`,
                   OCTET_LENGTH(`job`.`file_contents`) AS `job_size`,
                   `user`.`username` AS `user_name`, `user`.`full_name` AS `user_fullname`,
                   `task`.`id` AS `task_id`,
                   `task`.`page_name` AS `task_page_name`, task.`title` AS `task_title`,
                   `task`.`security` AS `task_security`,
                   `task`.`user_id` AS `task_owner_id`,
                   `task`.`open_source` AS `task_open_source`,
                   `task`.`open_tests` AS `task_open_tests`,
                   `task`.`public_tests` AS `task_public_tests`,
                   `task`.`test_count` AS `task_test_count`,
                   `round`.`id` AS `round_id`,
                   `round`.`page_name` AS `round_page_name`,
                   `round`.`title` AS `round_title`,
                   `round`.`type` AS `round_type`,
                   `round`.`state` AS `round_state`,
                   `round`.`public_eval` AS `round_public_eval`,
                   `round`.`start_time` AS `round_start_time`';
    if ($contents) {
        $field_list .= ", job.file_contents";
    }
    $query = sprintf("
              SELECT $field_list
              FROM ia_job AS job
              LEFT JOIN `ia_task` AS `task` ON `job`.`task_id` = `task`.`id`
              LEFT JOIN `ia_user` AS `user` ON `job`.`user_id` = `user`.`id`
              LEFT JOIN `ia_round` AS `round` ON `job`.`round_id` = `round`.`id`
              WHERE `job`.`id` = %d", $job_id);
    return db_fetch($query);
}

// Updates ia_job_test table
function job_test_update($job_id, $test_number, $test_group, $exec_time, $mem_limit,
                         $grader_exec_time, $grader_mem_limit, $points, $grader_msg) {
    $query = sprintf("DELETE FROM ia_job_test WHERE job_id = '%s' AND test_number = '%s'",
                     db_escape($job_id), db_escape($test_number));
    db_query($query);
    $query = sprintf("INSERT INTO ia_job_test
                     (job_id, test_number, test_group, exec_time, mem_used,
                      grader_exec_time, grader_mem_used, points, grader_message)
                     VALUES ('%s', %s, %s, %s, %s, %s, %s, '%s', '%s')",
                     db_escape($job_id), db_escape($test_number), db_escape($test_group),
                     db_escape($exec_time), db_escape($mem_limit),
                     db_escape($grader_exec_time, true),
                     db_escape($grader_mem_limit, true),
                     db_escape($points), db_escape($grader_msg));
    return db_query($query);
}

// Returns an array of test informations for a job, ordered by test group
function job_test_get_all($job_id) {
    $query = sprintf("SELECT * FROM `ia_job_test`
                      WHERE job_id = '%s' ORDER BY test_group, test_number",
                     db_escape($job_id));
    return db_fetch_all($query);
}


// Returns an array of public test information for a job
function job_test_get_public($job_id, $public_tests, $test_count) {
    $test_ids = task_parse_test_group($public_tests, $test_count);
    if (!count($test_ids)) {
        return array();
    }

    $query = sprintf("
        SELECT * FROM `ia_job_test`
        WHERE `job_id` = %s AND `test_number` IN (%s)
        ORDER BY `test_number`",
        db_quote($job_id),
        implode(", ", array_map("db_quote", array_values($test_ids))));
    return db_fetch_all($query);
}

/**
 * Counts the number of waiting jobs on the given user only in 'archive' type
 * rounds
 *
 * @param array @user
 * @return int
 */
function job_archive_waiting_number($user) {
    $query = sprintf("
        SELECT COUNT(*) FROM `ia_job`
        LEFT JOIN `ia_round` ON `ia_round`.`id` = `ia_job`.`round_id` AND
            `ia_round`.`type` = 'archive'
        WHERE `ia_job`.`user_id` = %d AND `ia_job`.`status` = 'waiting'",
            db_quote($user['id']));
    return db_query_value($query);
}

function job_get_by_task_id_user_ids_status(
  string $task_id, array $user_ids, string $status): array {
    $query = sprintf(
        'select * from ia_job '.
        'where task_id = "%s" ' .
        'and user_id in (%s) ' .
        'and status = "%s"',
        $task_id, implode(',', $user_ids), $status);
    return db_fetch_all($query);
}

function job_count_by_task_id_user_ids_status(
  string $task_id, array $user_ids, string $status): int {
    $query = sprintf(
        'select count(*) from ia_job '.
        'where task_id = "%s" ' .
        'and user_id in (%s) ' .
        'and status = "%s"',
        $task_id, implode(',', $user_ids), $status);
    return db_query_value($query);
}

function job_get_by_task_id_status(string $task_id, string $status): array {
    $query = sprintf(
        'select * from ia_job '.
        'where task_id = "%s" ' .
        'and status = "%s" ' .
        'order by id',
        $task_id, $status);
    return db_fetch_all($query);
}

function job_count_by_task_id_status(string $task_id, string $status): int {
    $query = sprintf(
        'select count(*) from ia_job '.
        'where task_id = "%s" ' .
        'and status = "%s" ' .
        'order by id',
        $task_id, $status);
    return db_query_value($query);
}
