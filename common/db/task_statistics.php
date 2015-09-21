<?php

require_once IA_ROOT_DIR.'common/db/db.php';
require_once IA_ROOT_DIR.'common/statistics-config.php';

/*
   Returns an array of maps containing the best $max_top_size user
   for the task $task_id, the criteria $criteria and round $round_id.
   The elements are sorted by their special_score and submit_time.
   $criteria = { 'memory', 'time', 'size' }
*/
function task_statistics_get_top_users($task_id,
                                       $criteria,
                                       $round_id,
                                       $max_top_size) {
    $query = sprintf("
        SELECT top.`user_id`,
               users.`username`,
               users.`full_name`,
               users.`rating_cache` AS rating,
               top.`special_score`,
               top.`job_id`
        FROM `ia_score_task_top_users` AS top
        INNER JOIN `ia_user` AS users
        ON top.`user_id` = users.`id`
        WHERE top.`criteria` = '%s'
        AND top.`task_id` = '%s'
        AND top.`round_id` = '%s'
        ORDER BY top.`special_score` ASC, top.`submit_time` ASC
        LIMIT %d",
        db_escape($criteria),
        db_escape($task_id),
        db_escape($round_id),
        $max_top_size);
    return db_fetch_all($query);
}

/*
  Returns a map containing the best $max_top_size users
  for the task $task_id and round $round_id grouped by the criteria.

  The structure of the map is as follows:
  (
    'memory' => (
                  (
                    user_id,
                    special_score
                  ),
                  (...),
                  ...
                ),
    'time' => ...,
    'size' => ...
  )

  The elements within a criteria are sorted by their 'special_score'.
*/
function task_statistics_get_all_top_users($task_id,
                                           $round_id,
                                           $max_top_size) {
    $criterias = array('memory', 'time', 'size');
    $result = array();
    foreach ($criterias as $criteria) {
        $current_result = task_statistics_get_top_users($task_id,
                                                        $criteria,
                                                        $round_id,
                                                        $max_top_size);
        unset($current_result['username']);
        unset($current_result['full_name']);
        unset($current_result['rating']);
        $result[$criteria] = $current_result;
    }
    return $result;
}

function task_statistics_delete_user_from_top($user_id,
                                              $task_id,
                                              $criteria,
                                              $round_id) {
    $query = sprintf("
        DELETE FROM `ia_score_task_top_users`
        WHERE `user_id` = %d
        AND `task_id` = '%s'
        AND `criteria` = '%s'
        AND `round_id` = '%s'",
        $user_id,
        db_escape($task_id),
        db_escape($criteria),
        db_escape($round_id));
    db_query($query);
}

function task_statistics_insert_or_update_user_in_top($user_id,
                                                      $task_id,
                                                      $criteria,
                                                      $round_id,
                                                      $special_score,
                                                      $submit_time,
                                                      $job_id) {
    $query = sprintf("
        INSERT INTO `ia_score_task_top_users`(`task_id`,
                                              `round_id`,
                                              `user_id`,
                                              `criteria`,
                                              `special_score`,
                                              `submit_time`,
                                              `job_id`)
        VALUES('%s', '%s', %d, '%s', %d, '%s', %d)
        ON DUPLICATE KEY UPDATE
            `special_score` = VALUES(`special_score`),
            `submit_time` = VALUES(`submit_time`),
            `job_id` = VALUES(`job_id`)",
        db_escape($task_id),
        db_escape($round_id),
        $user_id,
        db_escape($criteria),
        $special_score,
        db_escape($submit_time),
        $job_id);
    db_query($query);
}

function task_statistics_get_average_wrong_submissions($task_id, $round_id) {
    $query = sprintf("
        SELECT IFNULL(AVG(`incorrect_submits`),0.0)
        FROM `ia_score_user_round_task`
        WHERE `task_id` = '%s'
        AND `round_id` = '%s'
        AND `submits` > 0",
        db_escape($task_id),
        db_escape($round_id));
    return db_query_value($query, 0.0);
}

// Returns the number of incorrect submissions
// of $user_id for $task_id and $round_id
function task_statistics_get_user_wrong_submissions($task_id,
                                                    $user_id,
                                                    $round_id) {
    $query = sprintf("
        SELECT `incorrect_submits`
        FROM `ia_score_user_round_task`
        WHERE `task_id` = '%s'
        AND `user_id` = %d
        AND `round_id` = '%s'
        AND `submits` > 0",
        db_escape($task_id),
        $user_id,
        db_escape($round_id));
    return db_query_value($query, 0);
}

// Returns the ratio between the number of users who solved the problem
// and the number of users who attempted the problem
// measured in percentages
function task_statistics_get_solved_percentage($task_id, $round_id) {
    $query = sprintf("
        SELECT solved.count / attempted.count
        FROM (
           SELECT COUNT(*) AS count
           FROM `ia_score_user_round_task`
           WHERE `task_id` = '%s'
           AND `score` = 100
           AND `round_id` = '%s'
           AND `submits` > 0
          ) AS solved,
          (
           SELECT COUNT(*) AS count
           FROM `ia_score_user_round_task`
           WHERE `task_id` = '%s'
           AND `round_id` = '%s'
           AND `submits` > 0
          ) AS attempted",
        db_escape($task_id),
        db_escape($round_id),
        db_escape($task_id),
        db_escape($round_id));
    $percentage = db_query_value($query, 0.0);
    $percentage = round($percentage * 100, 1);
    return $percentage;
}

// Returns a map associating scores in the range 0..100
// to the number of users who have that score
// for problem $task_id and round $round_id
// The scores that appear in the map are the ones that either
// have their count > 0 or are a multiple of 5
function task_statistics_get_points_distribution($task_id, $round_id) {
    $query = sprintf("
        SELECT `score`, COUNT(*) AS count
        FROM `ia_score_user_round_task`
        WHERE `round_id` = '%s'
        AND `task_id` = '%s'
        AND `submits` > 0
        GROUP BY `score`",
        db_escape($round_id),
        db_escape($task_id));
    $result = db_fetch_all($query);

    $score_counts = array();
    foreach ($result as $key => $value) {
        $score_counts[(int) $value['score']] = $value['count'];
    }

    for ($score = 0; $score <= 100; $score += 5) {
        if (!array_key_exists($score, $score_counts)) {
            $score_counts[$score] = 0;
        }
    }
    ksort($score_counts);

    return $score_counts;
}

// Returns the special score of a job described by $file_size and $test_results
// for the criteria $criteria
function task_statistics_compute_special_score($criteria,
                                               $file_size,
                                               $test_results) {
    if ($criteria === 'size') {
        return $file_size;
    }
    $criteria_key = array(
        'time' => 'test_time',
        'memory' => 'test_mem',
    );

    $special_score = 0;
    foreach ($test_results as $test_result) {
        $special_score = max($special_score,
                             $test_result[$criteria_key[$criteria]]);
    }
    return $special_score;
}

// Updates the top users of $task_id with the new job
// described by the parameters for all of the criterias
function task_statistics_update_top_users($user_id,
                                          $task_id,
                                          $round_id,
                                          $score,
                                          $submit_time,
                                          $job_id,
                                          $file_size,
                                          $test_results) {
    if ($score < 100) {
        // The job doesn't qualify to enter the top. Ignore.
        return;
    }

    $top_users = task_statistics_get_all_top_users($task_id,
                                                   $round_id,
                                                   IA_STATISTICS_MAX_TOP_SIZE);
    // All of the tops are the same size
    $top_size = count($top_users['time']);

    foreach ($top_users as $criteria => $users) {
        $special_score = task_statistics_compute_special_score(
                                                            $criteria,
                                                            $file_size,
                                                            $test_results);

        // Check if the user is already in the top
        $special_score_in_top = -1;
        foreach ($users as $user) {
            if ($user['user_id'] === $user_id) {
                $special_score_in_top = $user['special_score'];
                break;
            }
        }

        $include_or_update_in_top = false;
        if ($special_score_in_top !== -1) {
            // The user is in the top. Check if this submission is better
            if ($special_score < $special_score_in_top) {
                $include_or_update_in_top = true;
            }
        } else {
            if ($top_size < IA_STATISTICS_MAX_TOP_SIZE) {
                // The top contains few users, include no matter what
                $include_or_update_in_top = true;
            } else {
                // Decide if this job can be included in the top
                // There is at least one user in the top
                $worst = $users[count($users) - 1];
                if ($special_score < $worst['special_score']) {
                    $include_or_update_in_top = true;
                    // Delete the user from the last place in the top
                    task_statistics_delete_user_from_top($worst['user_id'],
                                                         $task_id,
                                                         $criteria,
                                                         $round_id);
                }
            }
        }

        if ($include_or_update_in_top) {
            task_statistics_insert_or_update_user_in_top($user_id,
                                                         $task_id,
                                                         $criteria,
                                                         $round_id,
                                                         $special_score,
                                                         $submit_time,
                                                         $job_id);
        }
    }
}
