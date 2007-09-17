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
function job_create($task_id, $round_id, $user_id, $compiler_id, $file_contents) {
    $query = <<<SQL
        INSERT INTO ia_job
            (`task_id`, `round_id`, `user_id`, `compiler_id`, `file_contents`, `submit_time`)
        VALUES (%s, %s, %s, %s, %s, %s)
SQL;
    $query = sprintf($query,
            db_quote($task_id), db_quote($round_id), db_quote($user_id),
            db_quote($compiler_id), db_quote($file_contents), db_quote(db_date_format()));
    return db_query($query);
}

// Get something for the evaluator to do.
// Null if nothing is found.
function job_get_next_job() {
    $query = <<<SQL
SELECT `job`.`id`, `job`.`user_id`, `job`.`task_id`, `job`.`round_id`,
       `job`.`compiler_id`, `job`.`status`, `job`.`submit_time`,
       `job`.`eval_message`, `job`.`score`, `job`.`file_contents`
    FROM `ia_job` AS `job`
    INNER JOIN `ia_user` AS `user` ON `user`.`id` = `job`.`user_id`
    LEFT JOIN `ia_round` AS `round` ON `round`.`id` = `job`.`round_id`
    WHERE (`status` != 'done')
      AND ((`round`.`allow_eval` = TRUE) OR
           (`round`.`id` IS NULL) OR
           (`user`.`security_level` = 'admin'))
    ORDER BY `submit_time` ASC LIMIT 1
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
    $field_list = "`job`.`id`, job.`user_id`, `job`.`compiler_id`, `job`.`status`,
                   `job`.`submit_time`, `job`.`eval_message`, `job`.`score`, `job`.`eval_log`,
                   `user`.`username` as `user_name`, `user`.`full_name` as `user_fullname`,
                   `task`.`id` AS `task_id`,
                   `task`.`page_name` AS `task_page_name`, task.`title` AS `task_title`,
                   `task`.`hidden` AS `task_hidden`, `task`.`user_id` AS `task_owner_id`,
                   `round`.`id` AS `round_id`,
                   `round`.`page_name` AS `round_page_name`, `round`.`title` AS `round_title`";
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

// Return list of JOIN clauses for given filters
function job_get_table_joins($filters) {
    $sql = "";

    if (getattr($filters, 'task_hidden')) {
        $sql .= "\nLEFT JOIN `ia_task` AS `task`
                    ON `job`.`task_id` = `task`.`id`";
    }

    if (getattr($filters, 'user')) {
        $sql .= "\nLEFT JOIN `ia_user` AS `user`
                    ON `job`.`user_id` = `user`.`id`";
    }

    return $sql;
}

// Returns a where clause array based on complex filters.
function job_get_range_wheres($filters) {
    $user = getattr($filters, 'user');
    $task_hidden = getattr($filters, 'task_hidden');

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
    if (!is_null($task)) {
        $wheres[] = sprintf("`job`.`task_id` = '%s'", db_escape($task));
    }
    if (!is_null($user)) {
        $wheres[] = sprintf("`user`.`username` = '%s'", db_escape($user));
    }
    if (!is_null($round)) {
        $wheres[] = sprintf("`job`.`round_id` = '%s'", db_escape($round));
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
    if (!is_null($score_begin) && is_whole_number($score_begin)) {
        $wheres[] = sprintf("`job`.`score` >= '%s'", db_escape($score_begin));
    }
    if (!is_null($score_end) && is_whole_number($score_end)) {
        $wheres[] = sprintf("`job`.`score` <= '%s'", db_escape($score_end));
    }
    if (!is_null($eval_msg)) {
        $wheres[] = sprintf("`job`.`eval_message` LIKE '%s%%'", db_escape($eval_msg));
    }
    if (!is_null($task_hidden) && is_whole_number($task_hidden)) {
        $wheres[] = sprintf("`task`.`hidden` = %s", db_escape($task_hidden));
    }

    return $wheres;
}

// Get a range of jobs, ordered by submit time. Really awesome filterss!
function job_get_range($filters, $start, $range) {
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert($start >= 0);
    log_assert($range >= 0);
    $query = <<<SQL
SELECT `job`.`id`,
       `job`.`user_id` as `user_id`,
       `job`.`round_id` as `round_id`,
       `job`.`task_id` as `task_id`, 
       `job`.`submit_time`, 
       `job`.`compiler_id`, 
       `job`.`status`,
       `job`.`score`, 
       `job`.`eval_message`,
       `job`.`eval_log`,
       `user`.`username` AS `user_name`, 
       `user`.`full_name` AS `user_fullname`,
       `task`.`page_name` AS `task_page_name`, 
       `task`.`title` AS `task_title`,
       `task`.`hidden` AS `task_hidden`, 
       `task`.`user_id` AS `task_owner_id`,
       `round`.`page_name` AS `round_page_name`, 
       `round`.`title` AS `round_title`
#       (CASE WHEN `status` = 'processing' THEN
#                (SELECT `value` FROM `ia_parameter_value`
#                 WHERE `ia_parameter_value`.`object_id` = `task_id` AND
#                       `ia_parameter_value`.`object_type` = 'task' AND
#                       `ia_parameter_value`.`parameter_id` = 'tests')
#                ELSE NULL END) AS `total_tests`,
#       (CASE WHEN `status` = 'processing' THEN
#                (SELECT COUNT(*) FROM `ia_job_test`
#                 WHERE `ia_job_test`.`job_id` = `job`.`id`)
#                ELSE NULL END) AS `done_tests`
      FROM `ia_job` AS `job`
      LEFT JOIN `ia_user` AS `user` ON `job`.`user_id` = `user`.`id`
      LEFT JOIN `ia_task` AS `task` ON `job`.`task_id` = `task`.`id`
      LEFT JOIN `ia_round` AS `round` ON `job`.`round_id` = `round`.`id`
SQL;

    $wheres = job_get_range_wheres($filters);
    $query .= " WHERE (".implode(") AND (", $wheres).")";
    $query .= sprintf(" ORDER BY `job`.`submit_time` DESC LIMIT %s, %s", $start, $range);

    return db_fetch_all($query);
}

// Counts jobs based on complex filterss
function job_get_count($filters) {
    $query = <<<SQL
SELECT COUNT(*) as `cnt`
      FROM `ia_job` AS `job`
SQL;

    $query .= job_get_table_joins($filters);

    $wheres = job_get_range_wheres($filters);
    $query .= " WHERE (".implode(") AND (", $wheres).")";

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
    $query .= " WHERE (".implode(") AND (", $wheres).")";
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
                     VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                     db_escape($job_id), db_escape($test_number), db_escape($test_group),
                     db_escape($exec_time), db_escape($mem_limit), db_escape($grader_exec_time),
                     db_escape($grader_mem_limit), db_escape($points), db_escape($grader_msg));
    return db_query($query);
}

// Returns an array of test informations for a job, ordered by test group
function job_test_get_all($job_id) {
    $query = sprintf("SELECT * FROM `ia_job_test` 
                      WHERE job_id = '%s' ORDER BY test_group, test_number",
                     db_escape($job_id));  
    return db_fetch_all($query);
}

?>
