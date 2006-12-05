<?php

require_once(IA_ROOT."common/db/db.php");

/**
 * Job
 */

// Creates new eval job
function job_create($task_id, $user_id, $compiler_id, $file_contents) {
    $query = "
        INSERT INTO ia_job
            (task_id, user_id, compiler_id, file_contents, `submit_time`, `next_eval`)
        VALUES ('%s', '%s', '%s', '%s', NOW(), NOW())
    ";
    $query = sprintf($query, db_escape($task_id),
                     db_escape($user_id), db_escape($compiler_id),
                     db_escape($file_contents));
    return db_query($query);
}

// Get something for the evaluator to do.
function job_get_next_job() {
    $query = "
        SELECT * FROM ia_job
        WHERE `status` != 'done' AND `next_eval` < NOW()
        ORDER BY `submit_time` ASC LIMIT 1
    ";
    return db_fetch($query);
}

// Mark job status and next_eval.
// Delay is the delay in seconds, defaults to 300(5 minutes)
function job_mark_delay($job_id, $status, $delay = 300) {
    log_assert($status == 'processing' || $status == 'waiting');
    log_assert($delay >= 0);
    $query = sprintf(
            "UPDATE `ia_job`
            SET `status` = '%s', `next_eval` = DATE_ADD(NOW(), INTERVAL %s SECOND)
            WHERE `id` = %s",
            db_escape($status), db_escape($delay), db_escape($job_id));
    return db_query($query);
}

// Mark a certain job as 'done'
function job_mark_done($job_id, $eval_log, $eval_message, $score) {
    log_assert(is_whole_number($job_id));
    log_assert(is_whole_number($score));
    log_assert($score >= 0 && $score <= 100);
    $query = sprintf("
            UPDATE `ia_job` SET
            `status` = 'done', `eval_log` = '%s',
            `eval_message` = '%s', `score` = '%s'
            WHERE `id` = '%s'",
            db_escape($eval_log), db_escape($eval_message), $score, $job_id);
    return db_query($query);
}

function job_get_by_id($job_id, $contents = false) {
    log_assert(is_whole_number($job_id));
    $field_list = "job.`id`, job.`user_id`, `task_id`, `compiler_id`, `status`,
                   `submit_time`, `eval_message`, `score`, `eval_log`,
                   task.`page_name` as task_page_name, task.`title` as task_title,
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

function job_get_range($start, $range) {
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert($start >= 0);
    $query = sprintf("
              SELECT job.`id`, job.`user_id`, `task_id`, `compiler_id`, `status`,
                    `submit_time`, `eval_message`, `score`,
                    task.`page_name` as task_page_name, task.`title` as task_title,
                    user.`username` as user_name, user.`full_name` as user_fullname
              FROM ia_job AS job
              LEFT JOIN ia_task AS task ON job.`task_id` = `task`.`id`
              LEFT JOIN ia_user AS user ON job.`user_id` = `user`.`id`
              ORDER BY job.`submit_time` DESC LIMIT %s, %s",
              $start, $range);
    return db_fetch_all($query);
}

function job_get_count() {
    $query = "SELECT COUNT(*) AS `cnt` FROM ia_job";
    $res = db_fetch($query);
    return $res['cnt'];
}

?>
