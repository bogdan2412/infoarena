<?php

require_once(IA_ROOT_DIR."common/db/db.php");

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
        $remote_ip_info = null) {
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

// Returns a where clause array based on complex filters
// that relate only to ia_job table
function job_get_range_wheres_job($filters) {
    $user = getattr($filters, 'user');
    $task_security = getattr($filters, 'task_security');

    $task = getattr($filters, 'task');
    $round = getattr($filters, 'round');
    $job_begin = getattr($filters, 'job_begin');
    $job_end = getattr($filters, 'job_end');
    $job_id = getattr($filters, 'job_id');
    $time_begin = getattr($filters, 'time_begin');
    $time_end = getattr($filters, 'time_end');
    $compiler = getattr($filters, 'compiler');
    $status = getattr($filters, 'status');
    $score_begin = getattr($filters, 'score_begin');
    $score_end = getattr($filters, 'score_end');
    $eval_msg = getattr($filters, 'eval_msg');
    $remote_ip_info = getattr($filters, 'remote_ip_info');

    $wheres = array("TRUE");
    if (!is_null($task)) {
        $wheres[] = sprintf("`job`.`task_id` = '%s'", db_escape($task));
    }
    if (!is_null($user)) {
        // In case of username filter, do a query on `ia_user` to get user_id
        // then add `ia_job` table where clause for user_id
        $query = sprintf("
            SELECT `id`
            FROM `ia_user`
            WHERE `username` = '%s'", db_escape($user));
        $user_id = db_fetch($query);
        $user_id = getattr($user_id, 'id', -1);
        $wheres[] = sprintf("`job`.`user_id` = %s", db_escape($user_id));
    }
    if (!is_null($round)) {
        if (is_array($round)) {
            $db_escaped_rounds = array();
            foreach ($round as $r) {
                $db_escaped_rounds[] = "'" . db_escape($r) . "'";
            }

            $wheres[] = sprintf("`job`.`round_id` IN (%s)",
                               implode(",", $db_escaped_rounds));
        } else {
            $wheres[] = sprintf("`job`.`round_id` = '%s'", db_escape($round));
        }
    }
    if (!is_null($job_begin) && is_whole_number($job_begin)) {
        $wheres[] = sprintf("`job`.`id` >= '%s'", db_escape($job_begin));
    }
    if (!is_null($job_end) && is_whole_number($job_end)) {
        $wheres[] = sprintf("`job`.`id` <= '%s'", db_escape($job_end));
    }
    if (!is_null($job_id) && is_whole_number($job_id)) {
        $wheres[] = sprintf("`job`.`id` = '%s'", db_escape($job_id));
    }
    if (!is_null($time_begin) && strtotime($time_begin) !== false) {
        $time_begin = db_date_format(strtotime($time_begin));
        $wheres[] = sprintf("`job`.`submit_time` >= '%s'", db_escape($time_begin));
    }
    if (!is_null($time_end) && strtotime($time_end) !== false) {
        $time_end = db_date_format(strtotime($time_end));
        $wheres[] = sprintf("`job`.`submit_time` <= '%s'", db_escape($time_end));
    }
    if (!is_null($compiler)) {
        $wheres[] = sprintf("`job`.`compiler_id` = '%s'", db_escape($compiler));
    }
    if (!is_null($status)) {
        $wheres[] = sprintf("`job`.`status` = '%s'", db_escape($status));
    }
    if (!is_null($eval_msg)) {
        $wheres[] = sprintf("`job`.`eval_message` LIKE '%s%%'", db_escape($eval_msg));
    }
    if (!is_null($remote_ip_info)) {
        // We allow remote_ip_info to contain % wildcards. This will make it a bit
        // easier to search for IP classes.
        $wheres[] = sprintf("`job`.`remote_ip_info` LIKE '%s'", db_escape($remote_ip_info));
    }

    return $wheres;
}

// Returns a where clause array based on complex filters
// that are not related to ia_job table
function job_get_range_wheres($filters) {
    $user = getattr($filters, 'user');
    $task_security = getattr($filters, 'task_security');

    $task = getattr($filters, 'task');
    $round = getattr($filters, 'round');
    $job_begin = getattr($filters, 'job_begin');
    $job_end = getattr($filters, 'job_end');
    $job_id = getattr($filters, 'job_id');
    $time_begin = getattr($filters, 'time_begin');
    $time_end = getattr($filters, 'time_end');
    $compiler = getattr($filters, 'compiler');
    $status = getattr($filters, 'status');
    $score_begin = getattr($filters, 'score_begin');
    $score_end = getattr($filters, 'score_end');
    $eval_msg = getattr($filters, 'eval_msg');

    $wheres = array("TRUE");
    if (!is_null($score_begin) && is_whole_number($score_begin)) {
        $wheres[] = sprintf("(`job`.`score` >= '%s') AND (`round`.`public_eval` = 1)", db_escape($score_begin));
    }
    if (!is_null($score_end) && is_whole_number($score_end)) {
        $wheres[] = sprintf("(`job`.`score` <= '%s') AND (`round`.`public_eval` = 1)", db_escape($score_end));
    }
    if (array_key_exists($task_security, task_get_security_types())) {
        $wheres[] = sprintf("`task`.`security` = %s",
            db_escape($task_security));
    }

    return $wheres;
}

// Get a range of jobs, ordered by submit time. Really awesome filterss!
function job_get_range($filters, $start, $range) {
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert($start >= 0);
    log_assert($range >= 0);

    $wheres = job_get_range_wheres($filters);
    $wheres_job = job_get_range_wheres_job($filters);

    $query = "
        SELECT
            `job`.`id`,
            `job`.`user_id` AS `user_id`,
            `job`.`round_id` AS `round_id`,
            `job`.`task_id` AS `task_id`,
            `job`.`submit_time`,
            `job`.`compiler_id`,
            `job`.`status`,
            `job`.`score`,
            `job`.`eval_message`,
            `job`.`eval_log`,
            `job`.`remote_ip_info`,
            OCTET_LENGTH(`job`.`file_contents`) AS `job_size`,
            `user`.`username` AS `user_name`,
            `user`.`full_name` AS `user_fullname`,
            `task`.`page_name` AS `task_page_name`,
            `task`.`title` AS `task_title`,
            `task`.`security` AS `task_security`,
            `task`.`user_id` AS `task_owner_id`,
            `task`.`open_source` AS `task_open_source`,
            `round`.`page_name` AS `round_page_name`,
            `round`.`title` AS `round_title`,
            `round`.`state` AS `round_state`,
            `round`.`type` AS `round_type`,
            `round`.`public_eval` AS `round_public_eval`
        #    (CASE WHEN `status` = 'processing' THEN
        #        (SELECT `value` FROM `ia_parameter_value` WHERE
        #            `ia_parameter_value`.`object_id` = `task_id` AND
        #            `ia_parameter_value`.`object_type` = 'task' AND
        #            `ia_parameter_value`.`parameter_id` = 'tests')
        #        ELSE NULL END) AS `total_tests`,
        #    (CASE WHEN `status` = 'processing' THEN
        #        (SELECT COUNT(*) FROM `ia_job_test`
        #            WHERE `ia_job_test`.`job_id` = `job`.`id`)
        #        ELSE NULL END) AS `done_tests`
        FROM `ia_job` AS `job`
        LEFT JOIN `ia_task` AS `task` ON `job`.`task_id` = `task`.`id`
        LEFT JOIN `ia_round` AS `round` ON `job`.`round_id` = `round`.`id`
        LEFT JOIN `ia_user` AS `user` ON `job`.`user_id` = `user`.`id`";

    if (!isset($wheres[1])) {
        // if we have no filters outside of `ia_job` table then optimize query
        $subquery = "
            SELECT `job`.`id` AS `ID`
            FROM `ia_job` as `job`
            WHERE (" . implode(") AND (", $wheres_job) . ")
            ORDER BY `id` DESC LIMIT {$start}, {$range}";

        $job_ids_fetched = db_fetch_all($subquery);
        $job_ids = array();
        foreach ($job_ids_fetched as $job_id) {
            $job_ids[] = $job_id["ID"];
        }
        if (empty($job_ids)) {
            return array();
        }
        $query .= "
            WHERE `job`.`id` IN (" . implode(", ", array_map('db_quote', $job_ids)) . ")
            ORDER BY `job`.`id` DESC";
    } else {
        // we have filters outside of `ia_job` table, we can't query in query
        $query .= "
            WHERE (".implode(") AND (", $wheres).") AND (".implode(") AND (", $wheres_job).")";
        $query .= sprintf(" ORDER BY `job`.`id` DESC LIMIT %s, %s", $start, $range);
    }

    $result = db_fetch_all($query);

    return $result;
}

// Counts jobs based on complex filters
function job_get_count($filters) {
    $query = "
        SELECT COUNT(*) AS `cnt`
        FROM
            `ia_job` AS `job`";

    if (getattr($filters, 'task_security')) {
        $query .= "
            LEFT JOIN `ia_task` AS `task` ON `job`.`task_id` = `task`.`id`";
    }

    // score_begin and score_end filters shouldn't work on rounds
    // without public eval, so we join with ia_round
    if (getattr($filters, 'score_begin') || getattr($filters, 'score_end')) {
        $query .= "
            LEFT JOIN `ia_round` AS `round` ON `job`.`round_id` = `round`.`id`";
    }

    $wheres = job_get_range_wheres($filters);
    $wheres_job = job_get_range_wheres_job($filters);
    $query .= "
        WHERE (".implode(") AND (", $wheres).") AND
              (".implode(") AND (", $wheres_job).")";

    $res = db_fetch($query);
    return $res['cnt'];
}

// Re-eval a bunch of jobs based on complex filterss
function job_reeval($filters) {
    $query = <<<SQL
UPDATE `ia_job` AS `job`
       LEFT JOIN `ia_user` AS `user` ON `job`.`user_id` = `user`.`id`
       LEFT JOIN `ia_task` AS `task` ON `job`.`task_id` = `task`.`id`
       LEFT JOIN `ia_round` AS `round` ON `job`.`round_id` = `round`.`id`
SET `job`.`status` = "waiting"
SQL;
    $wheres = job_get_range_wheres($filters);
    $wheres_job = job_get_range_wheres_job($filters);
    $query .= " WHERE (".implode(") AND (", $wheres).") AND (".implode(") AND (", $wheres_job).")";
    return db_query($query);
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
    string $task_id, array $user_ids, string $status) {
    $query = sprintf(
        'select * from ia_job '.
        'where task_id = "%s" ' .
        'and user_id in (%s) ' .
        'and status = "%s"',
        $task_id, implode(',', $user_ids), $status);
    return db_fetch_all($query);
}
