<?php

require_once("db.php");

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

// Mark a certain job as 'processing'
function job_mark_processing($job_id) {
    $query = sprintf(
            "UPDATE `ia_job`
            SET `status` = 'processing', `next_eval` = DATE_ADD(`next_eval`, INTERVAL 10 MINUTE)
            WHERE `id` = '%s'",
            db_escape($job_id));
    return db_query($query);
}

// Mark a certain job as 'done'
function job_mark_done($job_id, $eval_log, $eval_message, $score) {
    $query = sprintf("
            UPDATE `ia_job` SET
            `status` = 'done', `eval_log` = '%s',
            `eval_message` = '%s', `score` = '%s'
            WHERE `id` = '%s'",
            db_escape($eval_log), db_escape($eval_message), $score, $job_id);
    return db_query($query);
}

function job_get_by_id($job_id) {
    $query = sprintf("SELECT * FROM `ia_job` WHERE `id`='%s'",
                     db_escape($job_id));
    return db_fetch($query);
}

function job_get_range($start, $range) {
    log_assert($start >= 0);
    $query = sprintf("
              SELECT job.`id`, job.`user_id`, `task_id`, `compiler_id`, `status`,
                    `submit_time`, `eval_message`, `score`,
                    task.`name`, task.`title` as task_title,
                    user.`username` as username
              FROM ia_job AS job
              LEFT JOIN ia_textblock AS task
                    ON CONCAT(\"task/\", job.`task_id`) = task.`name`
              LEFT JOIN ia_user AS user
                    ON job.`user_id` = user.`id`
              ORDER BY job.`next_eval` DESC LIMIT %s, %s",
              $start, $range);
    return db_fetch_all($query);
}

function job_get_count() {
    $query = "SELECT COUNT(*) AS `cnt` FROM ia_job";
    $res = db_fetch($query);
    return $res['cnt'];
}

?>
