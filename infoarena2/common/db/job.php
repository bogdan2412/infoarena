<?php

require_once("db.php");

/**
 * Job
 */

// Creates new eval job
function job_create($task_id, $user_id, $compiler, $file_contents) {
    $query = "
        INSERT INTO ia_job
            (task_id, user_id, compiler_id, file_contents, `timestamp`)
        VALUES ('%s', '%s', '%s', '%s', NOW())
    ";
    $query = sprintf($query, db_escape($task_id),
                     db_escape($user_id), db_escape($compiler),
                     db_escape($file_contents));
    return db_query($query);      
}

// Get something for the evaluator to do.
function job_get_next_job() {
    $query = "
        SELECT id, task_id, user_id, compiler_id, file_contents,
                status, `timestamp` FROM ia_job
        WHERE status = 'waiting'
        ORDER BY `timestamp` ASC LIMIT 1
    ";
    return db_fetch($query);      
}

// Mark a certain job as 'processing'
function job_mark_processing($job_id) {
    $query = sprintf(
            "UPDATE ia_job SET status = 'processing' WHERE `id` = '%s'",
            db_escape($job_id));
    return db_query($query);
}

// Mark a certain job as 'processing'
function job_mark_done($job_id, $eval_log, $eval_message, $score) {
    $query = sprintf("
            UPDATE ia_job SET
            status = 'done', eval_log = '%s',
            eval_message = '%s', score = '%s'
            WHERE `id` = '%s'",
            db_escape($eval_log), db_escape($eval_message), $score, $job_id);
    return db_query($query);
}

function job_get_by_id($job_id) {
    $query = sprintf("SELECT id, task_id, user_id, compiler_id,
                             status, timestamp, eval_log, score, eval_message,
                             mark_eval
                      FROM ia_job WHERE `id`='%s'",
                     db_escape($job_id));
    return db_fetch($query);
}

function monitor_jobs_get_range($start, $range, $filter = null) {
    if ($start < 0) return;
    
    $query = "SELECT job.`id`, user.`username`,
                     job.`task_id`, task.`title` as task_title,
                     job.`status`, job.`timestamp`, job.`mark_eval`,
                     job.`score`, job.`eval_message`
              FROM ia_job AS job
                LEFT JOIN ia_user AS user ON job.`user_id` = user.`id`
                LEFT JOIN ia_textblock AS task
                    ON CONCAT(\"task/\", job.`task_id`) = task.`name`";
    if ($filter) {
        $query .= "WHERE " . $filter . " ";
    }
    $query .= "ORDER BY job.`mark_eval` ASC, job.`timestamp` DESC
               LIMIT " . $start . ", " . $range;
    return db_fetch_all($query);
}

function monitor_jobs_get_count($filter = null) {
    if ($filter == null) {
        $query = "SELECT COUNT(*) AS `cnt` FROM ia_job";
    } else {
        $query = "SELECT COUNT(*) AS `cnt`
                  FROM ia_job AS job
                    LEFT JOIN ia_user AS user ON job.`user_id` = user.`id`";
        if ($filter) {
            $query .= "WHERE " . $filter . " ";
        }
    }
    $res = db_fetch($query);
    return $res['cnt'];
}

?>
