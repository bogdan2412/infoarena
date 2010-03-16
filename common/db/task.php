<?php

require_once(IA_ROOT_DIR . "common/db/db.php");
require_once(IA_ROOT_DIR . "common/task.php");
require_once(IA_ROOT_DIR . "common/db/parameter.php");

// Add $task to cache if not null, return $task.
function _task_cache_add($task) {
    if (!is_null($task)) {
        log_assert_valid(task_validate($task));
        mem_cache_set("task-by-id:{$task['id']}", $task, IA_MEM_CACHE_TASK_EXPIRATION);
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
    return mem_cache_set("task-by-id:$task_id", db_fetch($query), IA_MEM_CACHE_TASK_EXPIRATION);
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
        require_once(IA_ROOT_DIR . "common/textblock.php");
        $replace = array("task_id" => $task['id'], "task_title" => ucfirst($task['id']));
        textblock_copy_replace("template/newtask", $task['page_name'],
                $replace, "task: {$task['id']}", $task['user_id'], $remote_ip_info);

        _task_cache_add($task);
    }
    return $res;
}

// Delete a task, including scores, jobs and page
// WARNING: This is irreversible.
function task_delete($task) {
    log_assert_valid(task_validate($task));

    // Delete task from cache
    _task_cache_delete($task);

    // Delete problem page
    textblock_delete($task["page_name"]);

    // Delete all scores received on task
    db_query("DELETE FROM `ia_score_user_round_task`
              WHERE `task_id` = " . db_quote($task["id"]));

    // Recompute round scores
    $query = "SELECT `round_id` FROM `ia_round_task`
                WHERE `task_id` = ".db_quote($task['id']);
    $rounds = db_fetch_all($query);

    foreach ($rounds as $round) {
        round_recompute_score($round['round_id']);
    }

    // Remove task from all rounds
    db_query("DELETE FROM `ia_round_task`
              WHERE `task_id` = " . db_quote($task["id"]));

    // Delete task jobs
    $job_ids_fetched = db_fetch_all("
        SELECT `id`
        FROM `ia_job`
        WHERE `task_id` = " . db_quote($task["id"]));

    $job_ids = array();
    foreach ($job_ids_fetched as $job) {
        $job_ids[] = (int)$job["id"];
    }

    if (count($job_ids)) {
        $formated_job_ids = implode(", ", array_map("db_quote", $job_ids));
        db_query("DELETE FROM `ia_job_test`
                  WHERE `job_id` IN ({$formated_job_ids})");
        db_query("DELETE FROM `ia_job`
                  WHERE `id` IN ({$formated_job_ids})");
    }

    // Delete task
    db_query("DELETE FROM `ia_task` WHERE `id` = '" . db_escape($task["id"]) . "'");

    // Delete all task parameters
    task_update_parameters($task["id"], array());
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
    $res = db_fetch_all("SELECT * FROM ia_task");
    foreach ($res as $task) {
        _task_cache_add($task);
    }
    return $res;
}

// Returns list of round ids that include this task
function task_get_parent_rounds($task_id, $force_no_cache=false) {
    log_assert(is_task_id($task_id));
    if (!$force_no_cache) {
        $result = mem_cache_get("task-rounds-by-id:$task_id");
        if ($result !== false) {
            return $result;
        }
    }

    $query = sprintf("
        SELECT DISTINCT round_id
        FROM ia_round_task
        WHERE task_id=%s
        ORDER BY round_id
    ", db_quote($task_id));

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
    foreach($rounds as $id => $round) {
        $round = round_get($rounds[$id]);
        if (!security_query($user_id, "round-submit", $round)) {
            unset($rounds[$id]);
        }
    }
    return array_values($rounds);
}

function task_get_authors($task_id, $no_cache = false) {
    log_assert(is_task_id($task_id), 'Invalid task_id');

    $authors = false;
    if (!$no_cache) {
        $authors = mem_cache_get("task-authors-by-id:".$task_id);
    }

    if ($authors === false) {
        $authors = tag_get("task", $task_id, "author");
        mem_cache_set("task-authors-by-id:".$task_id, $authors);
    }

    return $authors;
}

// Task filter
// Returns only tasks that contain all the tags
// and are in 'arhiva' or in 'arhiva-educationala'
// Task from 'arhiva-educationala' are shown first
function task_filter_by_tags($tag_ids, $scores = true, $user_id = null) {
    log_assert(is_array($tag_ids), "tag_ids must be an array");
    foreach ($tag_ids as $tag_id) {
        log_assert(is_tag_id($tag_id), "invalid tag id");
    }

    if (count($tag_ids) > 0) {
        $tag_filter = "AND ".tag_build_where('task', $tag_ids);
    } else {
        $tag_filter = "";
    }

    if ($user_id == null || $scores == false) {
        $join_score = "";
        $score_fields = "";
    } else {
        $join_score = "LEFT JOIN ia_score ON ia_score.user_id = ".db_quote($user_id)." AND
                            ia_score.round_id = round.id AND
                            ia_score.task_id = ia_task.id AND
                            ia_score.name = 'score'";
        $score_fields = "ia_score.score,";
    }

    $query = "SELECT ia_task.id AS task_id,
                ia_task.title AS task_title,
                ia_task.order AS 'order',
                ia_task.page_name AS page_name,
                ia_task.open_source AS open_source,
                ia_task.open_tests AS open_tests,
                round.id AS round_id,
                $score_fields
                round.title AS round_title
    FROM ia_task
    LEFT JOIN ia_round_task AS round_task ON round_task.task_id = ia_task.id
    LEFT JOIN ia_round AS round ON round.id = round_task.round_id
    $join_score
    WHERE (round.id = 'arhiva' OR round.id = 'arhiva-educationala')
        AND ia_task.hidden = '0' $tag_filter
    ORDER BY round.id DESC, ia_task.order";
    $tasks = db_fetch_all($query);

    return $tasks;
}
?>
