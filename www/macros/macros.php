<?php

// Get the file to include for a certain macro.
function get_macro_include_file($macro_name)
{
    $macro_name = strtolower($macro_name);
    return IA_ROOT_DIR . "www/macros/macro_$macro_name.php";
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
    return macro_error('Nu ai destule permisiuni pentru acest macro.');
}

function execute_macro($macro_name, $macro_args) {
    $macro_file = get_macro_include_file($macro_name);
//    return macro_message($macro_file);
    if (!is_readable($macro_file)) {
        return macro_error('Nu exista macro-ul "'.$macro_name.'".');
    }
    if ($macro_file !== '') {
        //log_print("Including $macro_file");
        require_once($macro_file);
    }
    $macro_func = 'macro_'.$macro_name;
    if (!function_exists($macro_func)) {
        return macro_error('Nu exista macro-ul: "'.$macro_name.'".');
    }
    return $macro_func($macro_args);
}

?>
