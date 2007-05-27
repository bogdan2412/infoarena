<?php

require_once(IA_ROOT_DIR.'common/common.php');

// Prefix an array path.
// FIXME: properly escape paths for javascript.
// FIXME: array_path_get
function array_path_prepend($prefix, $path)
{
    if (is_string($prefix)) {
        if (preg_match('/^[a-z][a-z0-9_]*$/i', $prefix)) {
            return ".$prefix$path";
        } else {
            return "['$prefix']$path";
        }
    } else if (is_int($prefix)) {
        return "[$prefix]$path";
    } else {
        log_error("Invalid prefix $prefix");
    }
}

// Validate a data hash against a kwalify-ish schema.
// FIXME: This is incomplete
function array_validate($data, $schema)
{
    $errors = array();

    // Default type is str.
    $type = getattr($schema, 'type', 'str');

    if ($type == 'seq') {
        $value_schema = $schema['sequence'];

        if (!is_array($data)) {
            $errors[] = ': Not a sequence (is_array false).';
            return $errors;
        }

        // Check every value. There is no easy way to tell sequences from maps in php,
        // so we check for consecutive integer indexes by hand.
        $index = 0;
        foreach ($data as $k => $v) {
            if ($k != $index) {
                $errors[] = ': Not a sequence, array keys are not all consecutive integers.';
            }
            ++$index;
            $value_errors = array_validate($v, $value_schema);
            foreach ($value_errors as $value_error) {
                $errors[] = array_path_prepend($k, $value_error);
            }
        }
        return $errors;
    } else if ($type == 'map') {
        $mapping_schema = $schema['mapping'];

        if (!is_array($data)) {
            $errors[] = ': Not a mapping (is_array false).';
            return $errors;
        }

        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $mapping_schema)) {
                $errors[] = array_path_prepend($k, ": Key $k undefined.");
                continue;
            }
            $value_schema = $mapping_schema[$k];

            // Validate value and copy errors
            $value_errors = array_validate($v, $value_schema);
            foreach ($value_errors as $value_error) {
                $errors[] = array_path_prepend($k, $value_error);
            }
        }

        // Now check for missing required values
        foreach ($mapping_schema as $k => $v) {
            if (array_key_exists('required', $v)) {
                if (!is_bool($v['required'])) {
                    log_error('Required must be a boolean value.');
                }
                if ($v['required'] && !array_key_exists($k, $data)) {
                    $errors[] = array_path_prepend($k, ": Required key $k missing.");
                }
            }
        }
        return $errors;
    } else {
        // Primitive types here.
        if ($type == 'str') {
            if (!is_string($data)) {
                return array(": Not a string.");
            }
        } elseif ($type == 'int') {
            if (!is_int($data)) {
                return array(": Not an integer.");
            }
        } elseif ($type == 'float') {
            if (!is_float($data)) {
                return array(": Not a float.");
            }
        } elseif ($type == 'number') {
            if (!is_float($data) && !is_int($data)) {
                return array(": Not a number(integer or float).");
            }
        } elseif ($type == 'text') {
            if (!is_string($data) && !is_float($data) && !is_int($data)) {
                return array(": Not text(number or string).");
            }
        } elseif ($type == 'bool') {
            if (!is_bool($data)) {
                return array(": Not a boolean.");
            }
        } elseif ($type == 'date') {
            if (!is_db_date($data)) {
                return array(": Not a datetime value. Valid values are YYYY-MM-DD HH:MM:SS with the time part optional");
            }
        } elseif ($type == 'scalar') {
            if (is_array($data)) {
                return array(": Not a scalar value.");
            }
        } elseif ($type != 'any') {
            log_error("Unrecognized type '$type'");
        }
 
        // Check numeric ranges
        if (array_key_exists('range', $schema)) {
            if ($type == 'int' || $type == 'float' || $type == 'number') {
                $errors = array_merge($errors, _array_validate_number_range($data, $schema['range']));
            } else {
                log_error("Range constraint only valid for number nodes.");
            }
            // FIXME: Date support.
        }

        // Check string lengths
        if (array_key_exists('length', $schema)) {
            if ($type == 'str' || $type == 'text') {
                $errors = array_merge($errors, _array_validate_string_length($data, $schema['length']));
            } else {
                log_error("Length constraint only valid for string nodes.");
            }
        }

        // Check enum values.
        if (array_key_exists('enum', $schema)) {
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
                    $errors[] = ": Invalid value '$data'";
                }
            }
        }

        // Check string regular expression patterns
        if (array_key_exists('pattern', $schema)) {
            if (!is_string($data) && !is_float($data) && !is_int($data)) {
                log_error("Pattern constraint only valid for text nodes.");
            }
            $str = (string)$data;
            if (!preg_match($schema['pattern'], $str)) {
                $errors[] = ": Doesn't match pattern {$schema['pattern']}.";
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
            $errors[] = ": Value out of range, $value > $max.";
        }
    }

    // Min value, inclusive
    if (array_key_exists('min', $range)) {
        $min = $range['min'];
        if (!is_int($min) && !is_float($min)) {
            log_error("Invalid range min value; not an int or float.");
        } else if ($value < $min) {
            $errors[] = ": Value out of range, $value < $min.";
        }
    }

    // Max value, inclusive
    if (array_key_exists('max-ex', $range)) {
        $max = $range['max'];
        if (!is_int($max) && !is_float($max)) {
            log_error("Invalid range max exclusive value; not an int or float.");
        } else if ($value >= $max) {
            $errors[] = ": Value out of range, $value >= $max.";
        }
    }

    // Min value, inclusive
    if (array_key_exists('min-ex', $range)) {
        $min = $range['min'];
        if (!is_int($min) && !is_float($min)) {
            log_error("Invalid range min exclusive value; not an int or float.");
        } else if ($value <= $min) {
            $errors[] = ": Value out of range, $value <= $min.";
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
            $errors[] = ": Length out of range, $len > $max.";
        }
    }

    // Min value, inclusive
    if (array_key_exists('min', $range)) {
        $min = $range['min'];
        if (!is_int($min)) {
            log_error("Invalid length min value; not an int.");
        } else if ($len < $min) {
            $errors[] = ": Length out of range, $len < $min.";
        }
    }

    // Max value, inclusive
    if (array_key_exists('max-ex', $range)) {
        $max = $range['max'];
        if (!is_int($max)) {
            log_error("Invalid length max exclusive value; not an int.");
        } else if ($len >= $max) {
            $errors[] = ": Length out of range, $len >= $max.";
        }
    }

    // Min value, inclusive
    if (array_key_exists('min-ex', $range)) {
        $min = $range['min'];
        if (!is_int($min)) {
            log_error("Invalid length min exclusive value; not an int.");
        } else if ($len <= $min) {
            $errors[] = ": Length out of range, $len <= $min.";
        }
    }

    return $errors;
}

?>
