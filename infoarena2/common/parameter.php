<?php

require_once(IA_ROOT . "common/db/db.php");

// FIXME: obliterate?
//
// Parses parameter value as stored in database and returns native PHP value
// $parameter_id    parameter type (id)
// $value           raw value string stored in database
function parameter_decode($parameter_id, $value) {
    $booleans = array('unique_output', 'okfiles', 'rating_update');
    $ints = array('memlimit', 'tests', 'rating_timestamp');
    $floats = array('timelimit');
    $strings = array('evaluator');

    if (in_array($parameter_id, $booleans)) {
        if ("1" == $value) {
            return true;
        }
        elseif ("0" == $value || "" == $value) {
            return false;
        }
        log_error("Invalid boolean value: ".$value);
    }
    elseif (in_array($parameter_id, $ints)) {
        if (is_whole_number($value)) {
            return (int)$value;
        }
        log_error("Invalid integer value: ".$value);
    }
    elseif (in_array($parameter_id, $floats)) {
        if (is_numeric($value)) {
            return (float)$value;
        }
        log_error("Invalid float value: ".$value);
    }
    elseif (in_array($parameter_id, $strings)) {
        // make sure $value is of type string
        if (is_string($value)) {
            return $value;
        }
        log_error("Invalid string value: ".$value);
    }

    log_error("Unknown parameter id: \"{$parameter_id}\". "
              ."Value is: \"{$value}\".");
}

?>
