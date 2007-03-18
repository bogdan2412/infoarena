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
    INNER JOIN `ia_round` AS `round` ON `round`.`id` = `job`.`round_id`
    WHERE (`status` != 'done')
      AND ((`round`.`allow_eval` = TRUE) OR
           (`user`.`security_level` = 'admin'))
    ORDER BY `submit_time` ASC LIMIT 1
SQL;
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

// Get a range of jobs, ordered by submit time.
// Returns an array, use this like list($jobs, $count) = 
function job_get_range($start, $range, $task = null, $user = null) {
    log_assert(is_whole_number($start));
    log_assert(is_whole_number($range));
    log_assert($start >= 0);
    log_assert($range >= 0);
    $query = <<<SQL
SELECT `job`.`id`, `job`.`user_id`, `job`.`compiler_id`, `job`.`status`,
       `job`.`submit_time`, `job`.`eval_message`, `job`.`score`,
       `user`.`username` AS `user_name`, `user`.`full_name` AS `user_fullname`,
       `task`.`id` AS `task_id`,
       `task`.`page_name` AS `task_page_name`, task.`title` AS `task_title`,
       `task`.`hidden` AS `task_hidden`, `task`.`user_id` AS `task_owner_id`,
       `round`.`id` AS `round_id`,
       `round`.`page_name` AS `round_page_name`, `round`.`title` AS `round_title`
      FROM `ia_job` AS `job`
      LEFT JOIN `ia_user` AS `user` ON `job`.`user_id` = `user`.`id`
      LEFT JOIN `ia_task` AS `task` ON `job`.`task_id` = `task`.`id`
      LEFT JOIN `ia_round` AS `round` ON `job`.`round_id` = `round`.`id`
SQL;
    if (!is_null($task)) {
        $query .= sprintf(" WHERE job.`task_id` = '%s'", db_escape($task));
    }
    if (!is_null($user)) {
        if (is_null($task)) {         
            $query .= " WHERE ";
        } 
        else {
            $query .= " AND ";
        }
        $query .= sprintf("user.`username` = '%s'", db_escape($user));
    }
    $query .= sprintf(" ORDER BY job.`submit_time` DESC LIMIT %s, %s", $start, $range);

    return db_fetch_all($query);
}

function job_get_count($task = null, $user = null) {
    $joins = array();
    $wheres = array("TRUE"); 
    if (!is_null($task)) {
        $wheres[] = sprintf("job.`task_id` = '%s'", db_escape($task));
    }
    if (!is_null($user)) {
        $joins[] = "LEFT JOIN `ia_user` AS `user` ON job.`user_id` = `user`.`id`";
        $wheres[] = sprintf("user.`username` = '%s'", db_escape($user));
    }

    $query = "SELECT COUNT(*) as `cnt`".
            "\nFROM `ia_job` AS `job`".
            "\n".implode(' ', $joins).
            "\nWHERE (".implode(') AND (', $wheres).')';

    $res = db_fetch($query);
    return $res['cnt'];
}

?>
