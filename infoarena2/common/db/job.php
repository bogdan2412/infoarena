<?php

require_once(IA_ROOT_DIR."common/db/db.php");

/**
 * Job
 */

// Creates new eval job
// FIXME: check args.
function job_create($task_id, $user_id, $compiler_id, $file_contents) {
    $query = "
        INSERT INTO ia_job
            (task_id, user_id, compiler_id, file_contents, `submit_time`)
        VALUES ('%s', '%s', '%s', '%s', '%s')
    ";
    $query = sprintf($query, db_escape($task_id),
                     db_escape($user_id), db_escape($compiler_id),
                     db_escape($file_contents),
                     db_escape(db_date_format()));
    return db_query($query);
}

// Get something for the evaluator to do.
// Null if nothing is found.
function job_get_next_job() {
    $query = "
        SELECT * FROM ia_job
        WHERE `status` != 'done'
        ORDER BY `submit_time` ASC LIMIT 1
    ";
    return db_fetch($query);
}

// Update job status.
// Null parameters doesn't update.
function job_update($job_id,
        $status = null,
        $eval_message = null,
        $eval_log = null,
        $score = null) {
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
    $field_list = "job.`id`, job.`user_id`, `task_id`, `compiler_id`, `status`,
                   `submit_time`, `eval_message`, `score`, `eval_log`,
                   task.`page_name` as task_page_name, task.`title` as task_title,
                   task.`hidden` as task_hidden, task.`user_id` as task_owner_id,  
                   user.`username` as user_name, user.`full_name` as user_fullname";
    if ($contents) {
        $field_list .= ", job.file_contents";
    }
    $query = sprintf("
              SELECT $field_list 
              FROM ia_job AS job
              LEFT JOIN ia_task AS task ON job.`task_id` = `task`.`id`
              LEFT JOIN ia_user AS user ON job.`user_id` = `user`.`id`
              WHERE `job`.`id` = %d", $job_id);
    return db_fetch($query);
}

// Get a range of jobs, ordered by submit time.
function job_get_range($start, $range, $task = null, $user = null) {
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert($start >= 0);
    log_assert($range >= 0);
    $query = "SELECT job.`id`, job.`user_id`, `task_id`, `compiler_id`, `status`,
                    `submit_time`, `eval_message`, `score`,
                    task.`page_name` as task_page_name, task.`title` as task_title,
                    task.`hidden` as task_hidden, task.`user_id` as task_owner_id,  
                    user.`username` as user_name, user.`full_name` as user_fullname
              FROM ia_job AS job
              LEFT JOIN ia_task AS task ON job.`task_id` = `task`.`id`
              LEFT JOIN ia_user AS user ON job.`user_id` = `user`.`id`";
    if (!is_null($task)) {
        $query .= sprintf(" WHERE job.`task_id` = LCASE('%s')", db_escape($task));
    }
    if (!is_null($user)) {
        if (is_null($task)) {         
            $query .= " WHERE ";
        } 
        else {
            $query .= " AND ";
        }
        $query .= sprintf("user.`username` = LCASE('%s')", db_escape($user));
    }
    $query .= sprintf(" ORDER BY job.`submit_time` DESC LIMIT %s, %s", $start, $range);

    return db_fetch_all($query);
}

// Get total job count
function job_get_count($task = null, $user = null) {
    $query = "SELECT SQL_CALC_FOUND_ROWS 
                    job.`user_id`, `task_id`, 
                    user.`username` as user_name
              FROM ia_job AS job
              LEFT JOIN ia_user AS user ON job.`user_id` = `user`.`id`";
    if (!is_null($task)) {
        $query .= sprintf(" WHERE job.`task_id` = LCASE('%s')", db_escape($task));
    }
    if (!is_null($user)) {
        if (is_null($task)) {         
            $query .= " WHERE ";
        } 
        else {
            $query .= " AND ";
        }
        $query .= sprintf("user.`username` = LCASE('%s')", db_escape($user));
    }
    $res = db_fetch_all($query);
    return db_query_value("SELECT FOUND_ROWS()");
}

?>
