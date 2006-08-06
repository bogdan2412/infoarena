<?php
/**
 * This module contains various database-related functions and routines.
 *
 * Note: We keep database-persisted "models" very simple. Most of them are
 * simple dictionaries. 
 */

// first, we need a database connection
assert(!isset($dbLink));    // repetitive-include guard
$dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS)
          or die('Cannot connect to database.');
mysql_select_db(DB_NAME, $dbLink) or die ('Cannot select database.');

// Escapes a string to be safely included in a query.
function db_escape($str) {
    return mysql_escape_string($str);
}

function db_num_rows($res) {
    return mysql_num_rows($res);
}

// Executes query. Outputs error messages
// Returns native PHP mysql resource handle
function db_query($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
    if (!$result) {
        // An error has occured. Print helpful debug messages and die
        echo '<br/><br/><hr/><h1>SQL ERROR!</h1>';

        if (IA_SQL_TRACE) {
            echo '<p>' . mysql_error($dbLink) . '</p>';
            echo '<p>This has occured upon trying to execute this:</p>';
            echo '<pre>' . $query . '</pre>';
        }
        die();
    }
    return $result;
}

// Executes query, fetches only FIRST result
function db_fetch($query) {
    global $dbLink;
    $result = db_query($query);
    if ($result) {
        $row = mysql_fetch_assoc($result);
        if ($row === false) {
            return null;
        }
        return $row;
    }
    else {
        return null;
    }
}

// Executes query, fetches the whole result
function db_fetch_all($query) {
    global $dbLink;
    $result = db_query($query);
    if ($result) {
        $buffer = array();
        while ($row = mysql_fetch_assoc($result)) {
            $buffer[] = $row;
        }
        return $buffer;
    }
    else {
        return null;
    }
}

/**
 * Task
 */
function task_get($task_id) {
    $query = sprintf("SELECT * FROM ia_task WHERE `id` = LCASE('%s')",
                     db_escape($task_id));
    return db_fetch($query);
}

function task_get_textblock($task_id) {
    return textblock_get_revision('task/' . $task_id);
}

function task_create($task_id, $type, $hidden, $author, $source, $user_id) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_task
                        (`id`, `type`, `hidden`, author, `source`, user_id)
                      VALUES (LCASE('%s'), '%s', '%s', '%s', '%s')",
                      db_escape($task_id), db_escape($type),
                      db_escape($hidden), db_escape($author),
                      db_escape($source), db_escape($user_id));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function task_update($task_id, $type, $hidden, $author, $source) {
    global $dbLink;
    $query = sprintf("UPDATE ia_task
                      SET author = '%s', `source` = '%s', `type` = '%s',
                          `hidden` = '%s'
                      WHERE `id` = LCASE('%s')
                      LIMIT 1",
                     db_escape($author), db_escape($source),
                     db_escape($type), db_escape($hidden),
                     db_escape($task_id));
    return db_query($query);
}

// binding for parameter_get_values
function task_get_parameters($task_id) {
    return parameter_get_values('task', $task_id);
}

// binding for parameter_update_values
function task_update_parameters($task_id, $param_values) {
    return parameter_update_values('task', $task_id, $param_values);
}

// Returns array with all tasks available
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function task_list_info() {
    global $dbLink;
    $query = sprintf("SELECT ia_task.id AS id, tblock.title AS title,
                        ia_task.`hidden` AS `hidden`, ia_task.`type` AS `type`
                      FROM ia_task
                      LEFT JOIN ia_textblock AS tblock
                        ON tblock.`name` = CONCAT('task/', ia_task.id)
                      ORDER BY tblock.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

/**
 * Round
 */
function round_get($round_id) {
    $query = sprintf("SELECT * FROM ia_round WHERE `id` = LCASE('%s')",
                     db_escape($round_id));
    return db_fetch($query);
}

// Returns array with all open rounds
//
// :WARNING: This does not select all fields related to each round,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function round_get_info() {
    global $dbLink;
    $query = sprintf("SELECT ia_round.id AS id, ia_round.`type` AS `type`,
                             tblock.title AS title,
                             ia_round.`active` AS `active`,
                             ia_round.user_id AS user_id
                      FROM ia_round
                      LEFT JOIN ia_textblock AS tblock
                        ON tblock.`name` = CONCAT('round/', ia_round.id)
                      ORDER BY tblock.`title`");
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

function round_get_textblock($round_id) {
    return textblock_get_revision('round/' . $round_id);
}

function round_create($round_id, $type, $user_id, $active) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_round
                        (`id`, `type`, user_id, `active`)
                      VALUES (LCASE('%s'), '%s', '%s', '%s')",
                     db_escape($round_id), db_escape($type),
                     db_escape($user_id), db_escape($active));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function round_update($round_id, $type, $active) {
    global $dbLink;
    $query = sprintf("UPDATE ia_round
                      SET `type` = '%s', `active` = '%s'
                      WHERE `id` = LCASE('%s')
                      LIMIT 1",
                     db_escape($type), db_escape($active),
                     db_escape($round_id));
    return db_query($query);
}

// Returns array with all tasks attached to the specified round
//
// :WARNING: This does not select all fields related to each task,
// but rather chooses a few.
// Make sure that calls such as identity_require() have all necessary
// information to yield a correct answer.
function round_get_task_info($round_id) {
    global $dbLink;
    $query = sprintf("SELECT
                        task_id AS id, tblock.title AS title,
                        ia_task.`hidden` AS `hidden`,
                        ia_task.user_id AS user_id, ia_task.`type` AS `type`
                      FROM ia_round_task
                      LEFT JOIN ia_task ON ia_task.id = task_id
                      LEFT JOIN ia_textblock AS tblock
                        ON tblock.`name` = CONCAT('task/', task_id)
                      WHERE `round_id` = LCASE('%s')
                      ORDER BY tblock.`title`",
                     db_escape($round_id));
    $list = array();
    foreach (db_fetch_all($query) as $row) {
        $list[$row['id']] = $row;
    }
    return $list;
}

// binding for parameter_get_values
function round_get_parameters($round_id) {
    return parameter_get_values('round', $round_id);
}

// binding for parameter_update_values
function round_update_parameters($round_id, $param_values) {
    return parameter_update_values('round', $round_id, $param_values);
}

// Replaces attached task list for given round
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $tasks is array of task id's
function round_update_task_list($round_id, $tasks) {
    // delete all round-task relations
    $query = sprintf("DELETE FROM ia_round_task
                      WHERE round_id = LCASE('%s')",
                     db_escape($round_id));
    db_query($query);

    // insert new relations
    foreach ($tasks as $task_id) {
        $query = sprintf("INSERT INTO ia_round_task
                            (round_id, task_id)
                          VALUES ('%s', '%s')",
                         db_escape($round_id), db_escape($task_id));
        db_query($query);
    }
}

/**
 * Parameter
 */

// Lists all parameters of $type `type`.
// $type is "task" or "round"
function parameter_list($type) {
    $query = sprintf("SELECT * FROM ia_parameter WHERE `type` = '%s'",
                     db_escape($type));
    $dict = array();
    foreach (db_fetch_all($query) as $row) {
        $dict[$row['id']] = $row;
    }
    return $dict;
}

// Replaces all parameter values according to the given dictionary
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $object_type is "task" or "round"
function parameter_update_values($object_type, $object_id, $dict) {
    assert($object_type == 'task' or $object_type == 'round');

    // delete all parameters connected to this task
    $query = sprintf("DELETE FROM ia_parameter_value
                      WHERE object_type = '%s' AND object_id = LCASE('%s')",
                     db_escape($object_type), db_escape($object_id));
    db_query($query);

    // insert given parameters
    foreach ($dict as $k => $v) {
        $query = sprintf("INSERT INTO ia_parameter_value
                            (object_type, object_id, parameter_id, `value`)
                          VALUES ('%s', '%s', '%s', '%s')",
                         db_escape($object_type), db_escape($object_id),
                         db_escape($k), db_escape($v));
        db_query($query);
    }
}

// Returns hash with task parameter values
function parameter_get_values($object_type, $object_id) {
    $query = sprintf("SELECT *
                      FROM ia_parameter_value
                      WHERE object_type = '%s' AND object_id = LCASE('%s')",
                     db_escape($object_type), db_escape($object_id));
    $dict = array();
    foreach (db_fetch_all($query) as $row) {
        $dict[$row['parameter_id']] = $row['value'];
    }
    return $dict;
}

// Returns bool whether $value is a valid parameter value
function parameter_validate($parameter, $value) {
    return preg_match($parameter['validator'], $value);
}

/**
 * Textblocks and textblock revisions
 */
// Call this function to add a new revision.
function textblock_add_revision($name, $title, $content, $user_id) {
    global $dbLink;

    // do a query first
    $query = sprintf("SELECT title, text, user_id
                      FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'",
                     db_escape($name));
    $tmp = db_fetch($query);
    if ($tmp['title'] == $title && $tmp['text'] == $content &&
        $tmp['user_id'] = $user_id) return $tmp;
    // copy current version to revision table
    $query = sprintf("INSERT INTO ia_textblock_revision
                        SELECT *
                        FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'",
                     db_escape($name));
    db_query($query);
    // replace current version
    $query = sprintf("DELETE FROM ia_textblock
                      WHERE LCASE(`name`) = '%s'
                      LIMIT 1",
                     db_escape($name));
    db_query($query);
    $query = sprintf("INSERT INTO ia_textblock
                        (name, `text`, `title`, `timestamp`, user_id)
                      VALUES ('%s', '%s', '%s', NOW(), '%s')",
                     db_escape($name), db_escape($content),
                     db_escape($title), db_escape($user_id));
    return db_query($query);
}

function textblock_get_revision($name, $rev_num = null) {
    global $dbLink;
    if (is_null($rev_num)) {
        $query = sprintf("SELECT *
                          FROM ia_textblock
                          WHERE LCASE(`name`) = '%s'",
                         db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
    }
    else {
        $query = sprintf("SELECT *
                          FROM ia_textblock_revision
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

// returns an textblock
function textblock_get_revisions($name) {
    $query = sprintf("SELECT *
                      FROM ia_textblock_revision WHERE
                      LCASE(`name`) = '%s'
                      ORDER BY `timestamp`",
                     db_escape($name));
    return db_fetch_all($query);
}

// this obviously returns textblocoks without the actual content 
function textblock_get_revisions_without_content($name) {
    $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                      FROM ia_textblock_revision
                        LEFT JOIN ia_user ON
                            ia_textblock_revision.user_id = ia_user.id
                      WHERE LCASE(`name`) = '%s'
                      ORDER BY `timestamp`",
                     db_escape($name));
    return db_fetch_all($query);
}

function textblock_get_revision_without_content($name, $rev_num = null) {
    global $dbLink;
    if (is_null($rev_num)) {
        $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                          FROM ia_textblock
                          LEFT JOIN ia_user ON
                            ia_textblock.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`", db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
}
    else {
        $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                          FROM ia_textblock_revision LEFT JOIN ia_user ON
                          ia_textblock_revision.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

function textblock_get_revisions_with_username($name) {
    $query = sprintf("SELECT * FROM ia_textblock_revision LEFT JOIN ia_user
                      ON ia_textblock_revision.user_id = ia_user.id
                      WHERE LCASE(`name`)= '%s' ORDER BY `timestamp`",
                    db_escape($name));
    return db_fetch_all($query);
}

function textblock_get_revision_with_username($name, $rev_num = null) {
    global $dbLink;
    if (is_null($rev_num)) {
        $query = sprintf("SELECT *
                          FROM ia_textblock
                          LEFT JOIN ia_user ON
                            ia_textblock.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`", db_escape($name));
        $textblock = db_fetch($query);
        return $textblock;
    }
    else {
        $query = sprintf("SELECT *
                          FROM ia_textblock_revision LEFT JOIN ia_user ON
                          ia_textblock_revision.user_id = ia_user.id
                          WHERE LCASE(`name`) = '%s'
                          ORDER BY `timestamp`
                          LIMIT %s, 1",
                         db_escape($name), db_escape($rev_num));
        $textblock = db_fetch($query);
        return $textblock;
    }
}

function textblock_get_revision_count($name) {
    global $dbLink;
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock_revision
                      WHERE LCASE(`name`) = '%s'",
                    db_escape($name));
    $row = db_fetch($query);
    return $row['cnt'];
}

// Attention: these functions return textblocks without content.. is this ok?
function textblock_get_names($prefix) {
   $query = sprintf("SELECT `name`, title, user_id, `timestamp`
                     FROM ia_textblock
                     WHERE LCASE(`name`) LIKE '%s%%'
                     ORDER BY `name`",
                    db_escape($prefix));
    return db_fetch_all($query);
}

function textblock_get_names_with_user($prefix) {
    $query = sprintf("SELECT `name`, title, user_id, `timestamp`, username
                      FROM ia_textblock
                        LEFT JOIN ia_user ON ia_textblock.user_id = ia_user.id
                      WHERE LCASE(`name`) LIKE '%s%%'
                      ORDER BY `name`",
                     db_escape($prefix));
    return db_fetch_all($query);
}

/**
 * User
 */

function user_test_ia1_password($username, $password) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s' AND
                        SHA1(PASSWORD('%s')) = `password`",
                     db_escape($username), db_escape($password));
    return db_fetch($query);
}


function user_test_password($username, $password) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s' AND
                        SHA1('%s') = `password`",
                     db_escape($username), db_escape($password));
    return db_fetch($query);
}

function user_get_by_username($username) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(username) = '%s'",
                     db_escape($username));
    return db_fetch($query);
}

function user_get_by_email($email) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE LCASE(email) = '%s'",
                     db_escape($email));
    return db_fetch($query);
}

function user_get_by_id($id) {
    $query = sprintf("SELECT *
                      FROM ia_user
                      WHERE id = '%s'",
                     db_escape($id));
    return db_fetch($query);
}

function user_create($data) {
    global $dbLink;
    $query = "INSERT INTO ia_user (";
    foreach ($data as $key => $val) {
        $query .= '`' . $key . '`,';
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ') VALUES (';
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $query .= "SHA1('" . db_escape($val) . "'),";
        }
        else {
            $query .= "'" . db_escape($val) . "',";
        }
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= ')';

//    print $query; // debug info
    return db_query($query);
}

function user_update($data, $id)
{
    global $dbLink;
    $query = "UPDATE ia_user SET ";
    foreach ($data as $key => $val) {
        if ($key == 'password') {
            $query .= "`" . $key . "`=SHA1('" . db_escape($val) . "'),";
        }
        else {
            $query .= "`" . $key . "`='" . db_escape($val) . "',";
        }
    }
    $query = substr($query, 0, strlen($query)-1); // delete last ,
    $query .= " WHERE `id` = '" . db_escape($id) . "'";

//    print $query; // debug info
    return db_query($query);
}

/**
 * Attachment
 */
function attachment_get($name, $page) {
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                     db_escape($name), db_escape($page));
    return db_fetch($query);
}

function attachment_get_by_id($id) {
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE `id` = '%s'",
                     db_escape($id));
    return db_fetch($query);
}

function attachment_update($name, $size, $page, $user_id) {
    $query = sprintf("UPDATE ia_file
                      SET size = '%s', user_id ='%s', `timestamp` = NOW()
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                     db_escape($size), db_escape($user_id),
                     db_escape($name), db_escape($page));
    db_query($query);
    $query = sprintf("SELECT *
                      FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')",
                     db_escape($name), db_escape($page));
    $tmp = db_fetch($query);
    return $tmp['id'];
}

function attachment_insert($name, $size, $page, $user_id) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_file
                        (`name`, page, size, user_id, `timestamp`)
                      VALUES ('%s', '%s', '%s', '%s', NOW())",
                     db_escape($name), db_escape($page),
                     db_escape($size), db_escape($user_id));
    db_query($query);
    return mysql_insert_id($dbLink);
}

function attachment_delete($name, $page) {
    global $dbLink;
    $query = sprintf("DELETE FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND
                            LCASE(`page`) = LCASE('%s')
                      LIMIT 1",
                     db_escape($name), db_escape($page));
    return db_query($query);
}

function attachment_get_all($page) {
    $query = sprintf("SELECT *
                      FROM ia_file
                        LEFT JOIN ia_user ON ia_file.user_id = ia_user.id
                      WHERE LCASE(ia_file.page) = LCASE('%s')
                      ORDER BY ia_file.`timestamp` DESC",
                     db_escape($page));
    return db_fetch_all($query);
}

/**
 * News
 */
function news_get_range($start, $range, $prefix = null) {
    $query = sprintf("SELECT *
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'news/%s%%'
                      ORDER BY `timestamp` DESC
                      LIMIT %s,%s",
                     db_escape($prefix), db_escape($start), db_escape($range));
    return db_fetch_all($query);
}

function news_count() {
    $query = sprintf("SELECT COUNT(*) AS `cnt`
                      FROM ia_textblock
                      WHERE LCASE(`name`) LIKE 'news/%%'");
    $tmp = db_fetch($query);
    return $tmp['cnt'];
}

/**
 * Job
 */

// Creates new eval job
function job_create($round_id, $task_id, $user_id, $file_extension,
                    $file_contents) {
    $query = "
        INSERT INTO ia_job
            (round_id, task_id, user_id, file_extension, file_contents,
             `timestamp`)
        VALUES ('%s', '%s', '%s', '%s', '%s', NOW())
    ";
    $query = sprintf($query, db_escape($round_id), db_escape($task_id),
                     db_escape($user_id), db_escape($file_extension),
                     db_escape($file_contents));
    return db_query($query);      
}

// Get something for the evaluator to do.
function job_get_next_job() {
    $query = "
        SELECT id, round_id, task_id, user_id, file_extension, file_contents,
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
    $query = sprintf("SELECT id, round_id, task_id, user_id, file_extension,
                             status, timestamp, eval_log, score, eval_message,
                             mark_eval
                      FROM ia_job WHERE `id`='%s'",
                     db_escape($job_id));
    return db_fetch($query);
}

function monitor_jobs_get_range($start, $range, $filter = null) {
    if ($start < 0) return;
    
    $query = "SELECT job.`id`, user.`username`,
                     job.`task_id`, textblock.`title`,
                     job.`file_extension`,
                     job.`status`, job.`timestamp`,
                     job.`score`, job.`eval_message`,
                     job.`round_id`, job.`mark_eval`
              FROM ia_job AS job
                LEFT JOIN ia_user AS user ON job.`user_id` = user.`id`
                LEFT JOIN ia_textblock AS textblock
                    ON CONCAT(\"round/\", job.`round_id`) = textblock.`name`";
    if ($filter) {
        $query .= "WHERE " . $filter . " ";
    }
    $query .= "ORDER BY job.`mark_eval` ASC, job.`timestamp` DESC
               LIMIT " . $start . ", " . $range;
//    echo $query; // debug info
    return db_fetch_all($query);
}

function monitor_jobs_count_range($filter = null) {
    $query = "SELECT COUNT(*)
              FROM ia_job AS job
                LEFT JOIN ia_user AS user ON job.`user_id` = user.`id`
                LEFT JOIN ia_textblock AS textblock
                    ON CONCAT(\"round/\", job.`round_id`) = textblock.`name`";
    if ($filter) {
        $query .= "WHERE " . $filter . " ";
    }
    $res = db_fetch($query);
    return $res['COUNT(*)'];
}

function monitor_jobs_count() {
    $query = "SELECT COUNT(*) FROM ia_job";
    $res = db_fetch($query);
    return $res['COUNT(*)'];
}

?>
