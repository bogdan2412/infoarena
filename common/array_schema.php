<?php

require_once(IA_ROOT_DIR.'common/common.php');

// Get an array element.
// Path can be a string, int or array of string or ints.
//
// The $data array is navigated step by step; if it at any step on the way
// there is no non-null key then the default value is returned.
//
// Null values in $data are treated as non-existant, so
// array_get(array('ala' => null), 'ala', 'bala') will return 'bala'.
// This function uses isset rather than array_key_exists.
function array_get($data, $path, $default = null)
{
    if (!is_array($path)) {
        if (is_string($path) || is_int($path)) {
            // Optimize for single step.
            if (!is_array($data)) {
                return $default;
            }
            if (isset($data[$path])) {
                return $data[$path];
            } else {
                return $default;
            }
        } else {
            log_error("Invalid $path argument");
        }
    }
    while (count($path) > 0) {
        if (!is_set($path[0])) {
            log_error("Path must an array with integer keys");
        }
        if (!is_string($path[0]) && !is_int($path[0])) {
            log_error("Invalid array path step");
        }
        if (!is_array($data)) {
            return $default;
        }
        if (isset($data[$path[0]])) {
            $data = $data[array_shift($path)];
        } else {
            return $default;
        }
    }
    if (isset($data)) {
        return $data;
    } else {
        return $default;
    }
}

// Make a tiny local validation error.
function _local_error($message)
{
    return array('path' => array(), 'message' => $message);
}

// Validate a data hash against a schema.
// The schema is roughly inspire from kwalify, but there are significant
// differences. Please see the tests for samples.
// FIXME: This is incomplete
function array_validate($data, $schema)
{
    // Default nullable is false, values are required by default.
    // This is unlike SQL.
    $null = array_get($schema, 'null', false);
    if (is_null($data)) {
        if (!$null) {
            return array(_local_error("Required value missing."));
        } else {
            return array();
        }
    }

    // Create a static hash mapping types to validation functions.
    // Blazing fast.
    static $validation_funcs = array(
        'struct' => '_array_validate_struct',
        'sequence' => '_array_validate_sequence',
        'mapping' => '_array_validate_mapping',
        'string' => '_array_validate_string',
        'int' => '_array_validate_number',
        'float' => '_array_validate_number',
        'date' => '_array_validate_date',
        'any' => '_array_validate_any',
    );

    // Default type is string.
    $func = array_get($validation_funcs, $type = array_get($schema, 'type', 'string'));
    if ($func !== null) {
        $errors = $func($data, $schema);
    } else {
        // Invalid schema, crash.
        log_error("Unknown type '$type'");
    }

    // Call the validation callback, but only if all other tests passed.
    // This makes it a lot easier to write a validation callback function.
    // Errors from the validaton function are merged with the declarative
    // validation errors.
    if ($errors == array()) {
        $validation_function = array_get($schema, "callback", null);
        if (is_callable($validation_function)) {
            $errors = array_merge($errors, $validation_function($data, $schema));
        }
    }

    return $errors;
}

// Structs are php arrays with different constraints for each field. They
// are used among other things for database rows.
function _array_validate_struct($data, $schema)
{
    if (!is_array($data)) {
        return array(_local_error("Not a struct (is_array false)"));
    }

    $errors = array();
    $struct_fields = $schema['fields'];

    // Check defined fields first.
    foreach ($struct_fields as $field_name => $field_schema) {
        // Don't differentiate between null values and missing keys.
        // Such a distinction doesn't exist in some languages and it can be
        // very confusing.
        $field_value = array_get($data, $field_name, null);

        // Validate value and copy errors
        // If $field_schema specifies null then it doesn't matter if
        // $field_value doesn't exist, it will pass.
        $field_errors = array_validate($field_value, $field_schema);
        foreach ($field_errors as $field_error) {
            array_unshift($field_error['path'], $field_name);
            $errors[] = $field_error;
        }
    }

    // By default allow unknown values, but if the struct is marked as
    // sealed then report an error for extra fields.
    //
    // This will be rather tricky to extend for inherited constraints.
    if (array_get($schema, 'sealed', false)) {
        foreach ($data as $k => $v) {
            // Throw up on undefined keys, if marked as sealed.
            if (!array_get($struct_fields, $k)) {
                $errors[] = array(
                    'path' => array($k),
                    'message' => 'Field undefined',
                );
            }
        }
    }

    return $errors;
}

// Validate sequence types, returns $errors list.
// Sequences are simple C-style arrays, indexed with continuous integer values
// starting from 0.
function _array_validate_sequence($data, $schema)
{
    // Check if it's at least an array.
    if (!is_array($data)) {
        return array(_local_error("Not a sequence (is_array false)."));
    }

    $value_schema = $schema['values'];
    $errors = array();

    // Check every value. There is no easy way to tell sequences from
    // maps in php, so we check for consecutive integer indexes by hand.
    $index = 0;
    foreach ($data as $k => $v) {
        if ($k != $index) {
            $errors[] = _local_error("Not a sequence, array keys are not all consecutive integers.");
        }
        ++$index;
        $value_errors = array_validate($v, $value_schema);
        foreach ($value_errors as $value_error) {
            array_unshift($value_error['path'], $k);
            $errors[] = $value_error;
        }
    }

    return $errors;
}

// Validate mapping types.
// These are php arrays with constraints for values.
// FIXME: It's not possible to place constraints on keys.
// FIXME: How to properly report errors from key constraints?
function _array_validate_mapping($data, $schema)
{
    // Check if it's at least an array.
    if (!is_array($data)) {
        return array(_local_error("Not a mapping (is_array false)."));
    }

    $value_schema = $schema['values'];

    $errors = array();
    // Check every key/value pair.
    foreach ($data as $k => $v) {
        $value_errors = array_validate($v, $value_schema);
        foreach ($value_errors as $value_error) {
            array_unshift($value_error['path'], $k);
            $errors[] = $value_error;
        }
    }

    return $errors;
}

// Check string nodes.
// They can have length contraints similar to numeric ranges.
// They can be enums and thus restricted to a fixed set of values.
// They can also be forced to match a certain regex pattern.
function _array_validate_string($data, $schema)
{
    if (!is_string($data)) {
        return array(_local_error("Not a string."));
    }

    $errors = array();

    // Check length
    if (array_get($schema, 'length') != null) {
        $errors = array_merge($errors, _array_validate_string_length($data, $schema['length']));
    }

    // Check enum values.
    // FIXME: flipping the array if too slow, better ideas?
    if (array_get($schema, 'enum') != null) {
        if (!array_key_exists($data, array_flip($schema['enum']))) {
            $errors[] = _local_error("Invalid enum value '$data'");
        }
    }

    // Check string regular expression patterns.
    if (array_get($schema, 'pattern') != null) {
        if (!preg_match($schema['pattern'], $data)) {
            $errors[] = _local_error("Doesn't match pattern {$schema['pattern']}.");
        }
    }

    return $errors;
}

// Validate string length. Long and boring.
function _array_validate_string_length($value, $range)
{
    $errors = array();
    $len = strlen($value);

    // Max value, inclusive
    if (array_key_exists('max', $range)) {
        $max = $range['max'];
        if (!is_int($max)) {
            log_error("Invalid length max value; not an int.");
        } else if ($len > $max) {
            $errors[] = _local_error(": Length out of range, $len > $max.");
        }
    }

    // Min value, inclusive
    if (array_key_exists('min', $range)) {
        $min = $range['min'];
        if (!is_int($min)) {
            log_error("Invalid length min value; not an int.");
        } else if ($len < $min) {
            $errors[] = _local_error(": Length out of range, $len < $min.");
        }
    }

    // Max value, exclusive
    if (array_key_exists('max-ex', $range)) {
        $max = $range['max'];
        if (!is_int($max)) {
            log_error("Invalid length max exclusive value; not an int.");
        } else if ($len >= $max) {
            $errors[] = _local_error(": Length out of range, $len >= $max.");
        }
    }

    // Min value, exclusive
    if (array_key_exists('min-ex', $range)) {
        $min = $range['min'];
        if (!is_int($min)) {
            log_error("Invalid length min exclusive value; not an int.");
        } else if ($len <= $min) {
            $errors[] = _local_error(": Length out of range, $len <= $min.");
        }
    }

    return $errors;
}

// Validate numbers (ints or floats).
// $schema can have a 'range' field.
function _array_validate_number($data, $schema)
{
    $type = array_get($schema, 'type', 'string');

    // Check actual type.
    if ($type == 'int') {
        if (!is_int($data)) {
            return array(_local_error("Not an integer."));
        }
    } elseif ($type == 'float') {
        if (!is_float($data)) {
            return array(_local_error("Not a float."));
        }
    } else {
        log_error("Invalid type '$type' for _array_validate_number.");
    }

    // Check Ranges
    if (array_get($schema, 'range') != null) {
        return _array_validate_number_range($data, $schema['range']);
    } else {
        return array();
    }
}

// Validate ranges for int and float fields.
// This function is long and boring.
function _array_validate_number_range($value, $range)
{
    $errors = array();

    // Max value, inclusive
    if (array_key_exists('max', $range)) {
        $max = $range['max'];
        if (!is_int($max) && !is_float($max)) {
            log_error("Invalid range max value; not an int or float");
        } else if ($value > $max) {
            $errors[] = _local_error(": Value out of range, $value > $max.");
        }
    }

    // Min value, inclusive
    if (array_key_exists('min', $range)) {
        $min = $range['min'];
        if (!is_int($min) && !is_float($min)) {
            log_error("Invalid range min value; not an int or float.");
        } else if ($value < $min) {
            $errors[] = _local_error(": Value out of range, $value < $min.");
        }
    }

    // Max value, exclusive
    if (array_key_exists('max-ex', $range)) {
        $max = $range['max'];
        if (!is_int($max) && !is_float($max)) {
            log_error("Invalid range max exclusive value; not an int or float.");
        } else if ($value >= $max) {
            $errors[] = _local_error(": Value out of range, $value >= $max.");
        }
    }

    // Min value, exclusive
    if (array_key_exists('min-ex', $range)) {
        $min = $range['min'];
        if (!is_int($min) && !is_float($min)) {
            log_error("Invalid range min exclusive value; not an int or float.");
        } else if ($value <= $min) {
            $errors[] = _local_error(": Value out of range, $value <= $min.");
        }
    }

    return $errors;
}

// Validate dates. Dates are represented as strings in mysql's format,
// which is very similar with RFC 3339, without the T. In short, it's
// YYYY-MM-DD HH:MM:SS. This is simple, readable and can even be ordered
// using strcmp.
//
// FIXME: Support ranges. This is important.
function _array_validate_date($data, $schema)
{
    if (!is_db_date($data)) {
        return array(_local_error("Not a datetime value. Valid values are YYYY-MM-DD HH:MM:SS with the time part optional"));
    }

    // FIXME: Check date/time ranges.
    if (array_get($schema, 'range') != null) {
        log_error("Ranges not supported for dates yet");
    }

    return array();
}

// Validate booleans. There are no extra options for these.
function _array_validate_bool($data, $schema)
{
    if (!is_bool($data)) {
        return array(_local_error("Not a boolean."));
    }

    return array();
}

// Mock validation function for 'any' type.
// This can be used to only rely only the validation callback.
function _array_validate_any($data, $schema)
{
    return array();
}

?>
