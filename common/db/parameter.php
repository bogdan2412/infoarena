<?php

require_once(Config::ROOT."common/db/db.php");

// Round / task parameters
// This is sort of shared between rounds and tasks.

// Replaces all parameter values according to the given dictionary
// :WARNING: This function does not check for parameter validity!
// It only stores them to database.
//
// $object_type is "task" or "round"
// NOTE: magic caching enabled
function parameter_update_values($object_type, $object_id, $dict) {
    log_assert($object_type == 'task' || $object_type == 'round');

    // delete all parameters connected to this task
    $query = sprintf("DELETE FROM ia_parameter_value
                      WHERE object_type = %s AND object_id = %s",
                     db_quote($object_type), db_quote($object_id));
    db_query($query);

    // insert given parameters
    foreach ($dict as $k => $v) {
        $query = sprintf("INSERT INTO ia_parameter_value
                            (`object_type`, `object_id`, `parameter_id`, `value`)
                          VALUES (%s, %s, %s, %s)",
                         db_quote($object_type), db_quote($object_id),
                         db_quote($k), db_quote($v));
        db_query($query);
    }
}

// Returns hash with task parameter values
function parameter_get_values($object_type, $object_id) {
    log_assert($object_type == 'task' || $object_type == 'round');

    $query = sprintf("SELECT *
                      FROM ia_parameter_value
                      WHERE object_type = %s AND object_id = %s",
                     db_quote($object_type), db_quote($object_id));
    $dict = array();
    foreach (db_fetch_all($query) as $row) {
        $dict[$row['parameter_id']] = $row['value'];
    }

    return $dict;
}

// Creates or updates the value for a global parameter.
function parameter_update_global($id, $value) {
    // delete existing value if any
    $query = sprintf("DELETE FROM ia_parameter_value
                      WHERE object_type = 'global' AND parameter_id = %s",
                     db_quote($id));
    db_query($query);

    // insert given value
    $query = sprintf("INSERT INTO ia_parameter_value
                            (`object_type`, `parameter_id`, `value`)
                          VALUES ('global', %s, %s)",
                     db_quote($id), db_quote($value));
    db_query($query);
}

/**
 * Retrieves the value for a global parameter.
 * @param string $id Parameter name.
 * @param mixed $default Default value to return if the parameter is not set.
 **/
function parameter_get_global($id, $default = null) {
    $query = sprintf("SELECT *
                      FROM ia_parameter_value
                      WHERE object_type = 'global' AND parameter_id = %s",
                     db_quote($id));
    $rows = db_fetch_all($query);
    return $rows[0]['value'] ?? $default;
}

?>
