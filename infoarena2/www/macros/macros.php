<?php

// Table that maps macro names to include files.
// By convention the macro function is called macro_$name.
// If you place the macro in macro_$name.php you can skip
// adding it here.
$macro_file_map = array(
        "debug" => '',
);

function macro_debug($args)
{
    if (!identity_can('macro-debug')) {
        return make_error_div('Debug macro expands for administrators only.');
    }

    $res = "<p>Debug macro listing args</p>";
    $res .= '<pre>';
    $ncargs = $args;
    //unset($ncargs['context']);
    $res .= htmlentities(print_r($ncargs, true));
    $res .= '</pre>';

    return $res;
}

// Get the file to include for a certain macro.
function get_macro_include_file($macro_name)
{
    global $macro_file_map;
    $macro_name = strtolower($macro_name);
    if (array_key_exists($macro_name, $macro_file_map)) {
        return $macro_file_map[$macro_name];
    } else {
        return "macro_" . $macro_name . '.php';
    }
}

// Format an error message as a html div.
// Can be returned from macros.
function make_error_div($text)
{
    return '<div class="macroError">' . htmlentities($text) . '</div';
}

function execute_macro($macro_name, $macro_args)
{
    $macro_file = get_macro_include_file($macro_name);
    if ($macro_file !== '') {
        require_once($macro_file);
    }
    $macro_func = 'macro_'.$macro_name;
    if (!function_exists($macro_func)) {
        return make_error_div('Nu exista macro-ul "'.$macro_name.'"');
    }
    return $macro_func($macro_args);
}

?>
