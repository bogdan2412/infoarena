<?php

require_once(IA_ROOT_DIR."common/db/db.php");

// Round / task parameters
// This is sort of shared between rounds and tasks.

// Replaces all parameter values according to the given dictionary
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $object_type is "task" or "round"
function parameter_update_values($object_type, $object_id, $dict) {
    log_assert($object_type == 'task' || $object_type == 'round');

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

?>
