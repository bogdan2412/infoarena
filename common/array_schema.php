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
    return array('path' => array(), 'mesage' => $message);
}

// Validate a data hash against a schema.
// The schema is roughly inspire from kwalify, but there are significant
// differences. Please see the tests for samples.
// FIXME: This is incomplete
function array_validate($data, $schema)
{
    $errors = array();

    // Default type is str.
    $type = array_get($schema, 'type', 'str');

    if ($type == 'seq') {
        $value_schema = $schema['sequence'];

        if (!is_array($data)) {
            $errors[] = _local_error("Not a sequence (is_array false).");
            return $errors;
        }

        // Check every value. There is no easy way to tell sequences from maps in php,
        // so we check for consecutive integer indexes by hand.
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
    } else if ($type == 'map') {
        $mapping_schema = $schema['mapping'];

        if (!is_array($data)) {
            $errors[] = _local_error("Not a mapping (is_array false)");
            return $errors;
        }

        foreach ($data as $k => $v) {
            // Throw up on undefined keys.
            if (!array_get($mapping_schema, $k)) {
                $errors[] = array(
                    'path' => array($k),
                    'message' => 'Key undefined',
                );
                continue;
            }

            $value_schema = $mapping_schema[$k];

            // Validate value and copy errors
            $value_errors = array_validate($v, $value_schema);
            foreach ($value_errors as $value_error) {
                array_unshift($value_error['path'], $k);
                $errors[] = $value_error;
            }
        }

        // Now check for missing required values
        foreach ($mapping_schema as $k => $v) {
            if (array_key_exists('required', $v)) {
                if ($v['required'] && !array_key_exists($k, $data)) {
                    $errors[] = array(
                        'path' => array($k),
                        'message' => "Required key missing.",
                    );
                }
            }
        }

        return $errors;
    } else {
        // Primitive types here.
        if ($type == 'str') {
            if (!is_string($data)) {
                return array(_local_error("Not a string."));
            }
        } elseif ($type == 'int') {
            if (!is_int($data)) {
                return array(_local_error("Not an integer."));
            }
        } elseif ($type == 'float') {
            if (!is_float($data)) {
                return array(_local_error("Not a float."));
            }
        } elseif ($type == 'number') {
            if (!is_float($data) && !is_int($data)) {
                return array(_local_error("Not a number(integer or float)."));
            }
        } elseif ($type == 'text') {
            if (!is_string($data) && !is_float($data) && !is_int($data)) {
                return array(_local_error("Not text(number or string)."));
            }
        } elseif ($type == 'bool') {
            if (!is_bool($data)) {
                return array(_local_error("Not a boolean."));
            }
        } elseif ($type == 'date') {
            if (!is_db_date($data)) {
                return array(_local_error("Not a datetime value. Valid values are YYYY-MM-DD HH:MM:SS with the time part optional"));
            }
        } elseif ($type == 'scalar') {
            if (is_array(_local_error($data))) {
                return array(_local_error("Not a scalar value."));
            }
        } elseif ($type != 'any') {
            log_error("Unrecognized type '$type'");
        }
 
        // Check numeric ranges
        if (array_get($schema, 'range') != null) {
            if ($type == 'int' || $type == 'float' || $type == 'number') {
                $errors = array_merge($errors, _array_validate_number_range($data, $schema['range']));
            } else {
                log_error("Range constraint only valid for number nodes.");
            }
            // FIXME: Date support.
        }

        // Check string lengths
        if (array_get($schema, 'length') != null) {
            if ($type == 'str' || $type == 'text') {
                $errors = array_merge($errors, _array_validate_string_length($data, $schema['length']));
            } else {
                log_error("Length constraint only valid for string nodes.");
            }
        }

        // Check enum values.
        if (array_get($schema, 'enum') != null) {
            if (is_array($data)) {
                log_error("Enum constraints only valid for scalar nodes.");
            } else {
                $valid = false;
                foreach ($schema['enum'] as $key => $value) {
                    if ($value === $data) {
                        $valid = true;
                        break;
                    }
                }
                if (!$valid) {
                    $errors[] = _local_error("Invalid value '$data'");
                }
            }
        }

        // Check string regular expression patterns
        if (array_get($schema, 'pattern') != null) {
            if (!is_string($data) && !is_float($data) && !is_int($data)) {
                log_error("Pattern constraint only valid for text nodes.");
            }
            $str = (string)$data;
            if (!preg_match($schema['pattern'], $str)) {
                $errors[] = _local_error("Doesn't match pattern {$schema['pattern']}.");
            }
        }

        return $errors;
    }
}

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

    // Max value, inclusive
    if (array_key_exists('max-ex', $range)) {
        $max = $range['max'];
        if (!is_int($max) && !is_float($max)) {
            log_error("Invalid range max exclusive value; not an int or float.");
        } else if ($value >= $max) {
            $errors[] = _local_error(": Value out of range, $value >= $max.");
        }
    }

    // Min value, inclusive
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

    // Max value, inclusive
    if (array_key_exists('max-ex', $range)) {
        $max = $range['max'];
        if (!is_int($max)) {
            log_error("Invalid length max exclusive value; not an int.");
        } else if ($len >= $max) {
            $errors[] = _local_error(": Length out of range, $len >= $max.");
        }
    }

    // Min value, inclusive
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

?>
