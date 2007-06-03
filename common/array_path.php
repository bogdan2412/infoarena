<?php

require_once(IA_ROOT_DIR."common/common.php");

// NOTE: These functions are untested. They will become useful if we
// send array paths to javascript. Until then it's better to work with
// exploded array paths.

// Create an array path string from an array of steps (or a single step)
// Steps can only be strings or ints.
// Array paths can be queries with <item><path> in javascript.
// FIXME: properly escape javascript strings.
// FIXME: array_path_get
// FIXME: Optimize
//
// Examples:
//      array_path_join('bubbles') == '.bubbles';
//      array_path_join(2, 'ala bala', gigi) == '[2]['ala bala'].gigi;
function array_path_join($steps)
{
    if (!is_array($steps)) {
        $steps = array($steps);
    }
    $result = '';
    foreach ($steps as $step) {
        if (is_string($step)) {
            if (preg_match('/^[a-z_][a-z0-9_]*$/i', $step)) {
                $result .= $step;
            } else {
                $result .= "['$step']";
            }
        } else if (is_int($step)) {
            $result .= "[$step]";
        } else {
            log_error("Invalid array path step $step");
        }
    }
    return $result;
}

// Split an array path string into an array of steps.
// It's generally preferable to work with splitted paths.
// This function is rather heavy.
function array_path_split($path)
{
    if (!is_string($path)) {
        log_error("Invalid array path: Not a string.");
    }
    $result = array();
    $strlen = $count($path);
    $pos = 0;
    while ($path != '') {
        $step = '';

        // Process .identifier step.
        $char = $path[$pos];
        if ($char == '.') {
            if ($pos + 1 >= $len) {
                log_error("Invalid array path: Ends with dot.");
            }
            
            // Check first character
            $char = $path[++$pos];
            if (($char < 'a' || $char > 'z') &&
                ($char < 'A' || $char > 'Z') && $char != '_') {
                log_error("Invalid array path[$pos]: Bad identifier start.");
            }
            $step .= $char;

            // Now check the following
            while (true) {
                if ($pos + 1 >= $len) {
                    break;
                }
                $char = $path[++$pos];
                if ($char == '.') {
                    break;
                }

                if (($char < 'a' || $char > 'z') &&
                    ($char < 'A' || $char > 'Z') &&
                    ($char < '0' || $char > '9') && $char != '_') {
                    log_error("Invalid array path[$pos]: Bad identifier character.");
                }
                $step .= $char;
            }
            $result[] = $step;
            continue;
        }

        // Process ['string'] or [number] step.
        if ($char == '[') {
            // Check character after [
            if ($pos + 1 >= 2) {
                log_error('Invalid array path: Ends with [.');
            }
            $char = $path[++$pos];

            // Number. FIXME: hex and octal not supported, neither are float. This is OK.
            if ($char >= '0' && $char <= '9') {
                $span = strspn($path, '0123456789', $pos);
                if ($path[$span] != ']') {
                    log_error("Invalid array path: ] expected");
                }
                $result[] = (int)substr($path, $pos, $span);
                $pos += $span;
                continue;
            }

            // Now parse quoted strings
            if ($char != "'" && $char != '"') {
                log_error('Invalid array path: Quoted string or number expected in []');
            }
            $quote = $char;
            while (true) {
                if ($pos + 1 >= $len) {
                    log_error("Invalid array path: Unterminated [{$quote}string.");
                }
                $char = $path[++$pos];

                // Parse escape sequences
                if ($char == "\\") {
                    if ($pos + 1 >= $len) {
                        log_error("Invalid array path: Dangling \\.");
                    }
                    $char = $path[++$pos];

                    // Parse javascript escape sequences, as per JS string literal syntax.
                    static $simple_slash_escapes = array(
                        "'" => "'", '"' => '"', "\\" => "\\",
                        "n" => "\n", "r" => "\r",
                        "t" => "\t", "v" => "\v",
                        "b" => "\b", "f" => "\f",
                    );
                    if (isset($simple_slash_escapes[$char])) {
                        $step .= $simple_slash_escapes($path[$i + 1]);
                    } elseif ($char == 'x') {
                        // Parse \x hex ascii character escapes.
                        if ($pos + 2 >= $len) {
                            log_error("Invalid array path: Unterminated \\x sequnce.");
                        }
                        $char = $path[++$pos];
                        $step .= chr(ord($char) - ord('0'));
                        $char = $path[++$pos];
                        $step .= chr(ord($char) - ord('0'));
                    } elseif ($char == 'u') {
                        log_error("Invalid array path[$pos]: Unicode escape not supported.");
                    } elseif ($char >= 0 && $char <= 9) {
                        log_error("Invalid array path[$pos]: Octal escape not supported.");
                    } else {
                        log_error("Invalid array path[$pos]: Unrecognized escape sequence.");
                    }
                } else if ($char == $quote) {
                    break;
                } else if ($char >= 32 && $char < 127) {
                    // Printable characters
                    $step .= $char;
                } else {
                    log_error("Invalid array path[$pos]: Unprintable character found");
                }
            }
            // If we broke off then we found a properly quoted " or ' string.
            if ($pos + 1 >= $len || $path[$pos + 1] != ']') {
                log_error("Invalid array path[$pos]: ] expected");
            }
            $result[] = $step;
        }
    }
    return $result;
}

?>
