<?php

// Table that maps macro names to include files.
// By convention the macro function is called macro_$name.
// If you place the macro in macro_$name.php you can skip
// adding it here.
$macro_file_map = array(
);

// Get the file to include for a certain macro.
function get_macro_include_file($macro_name)
{
    global $macro_file_map;
    $macro_name = strtolower($macro_name);
    if (array_key_exists($macro_name, $macro_file_map)) {
        $file_name = $macro_file_map[$macro_name];
    } else {
        $file_name = "macro_" . $macro_name . '.php';
    }
    return IA_ROOT . "www/macros/" . $file_name;
}

// Format an error message as a html div.
// Can be returned from macros.
function macro_error($text, $raw_html = false) {
    return '<div class="macroError">' . ($raw_html ? $text : htmlentities($text)) . '</div>';
}

// Format message as a html div.
// Can be returned from macros.
function macro_message($text, $raw_html = false) {
    return '<div class="macroMessage">' . ($raw_html ? $text : htmlentities($text)) . '</div>';
}

// Preset error message for insufficient privileges.
function macro_permission_error() {
    return macro_error('Nu ai destul permisiuni pentru acest macro.');
}

function execute_macro($macro_name, $macro_args) {
    $macro_file = get_macro_include_file($macro_name);
//    return macro_message($macro_file);
    if (!is_readable($macro_file)) {
        return macro_error('Nu exista macro-ul "'.$macro_name.'".');
    }
    if ($macro_file !== '') {
        // FIXME: this kills log messages.
        require_once($macro_file);
    }
    $macro_func = 'macro_'.$macro_name;
    if (!function_exists($macro_func)) {
        return macro_error('Nu exista macro-ul: "'.$macro_name.'".');
    }
    return $macro_func($macro_args);
}

?>
