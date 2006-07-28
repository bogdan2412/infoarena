<?php

function request($paramName, $defaultValue = null) {
    return getattr($_REQUEST, $paramName, $defaultValue);
}

// Nicer way to get an element from an array. It returns a default value
// (defaulting to null) instead of throwing an error.
function getattr($dict, $attribute, $defaultValue = null) {
    if (isset($dict[$attribute])) {
        return $dict[$attribute];
    }
    else {
        return $defaultValue;
    }
}

// Call this function for a http-level redirect.
// NOTE: this function DOES NOT RETURN.
function redirect($absoluteUrl) {
    header("Location: {$absoluteUrl}\n\n");
    die();
}

// Get an url.
// The params array contains http get parameter,
// it's formatted in the end result as a series
// of key1=value1&key2=value2.
//
// NOTE: Only use this function for urls.
// NOTE: don't add ?x=y stuff in document.
function url($document, $params = array()) {
    assert(false === strpos($document, '?'));

    $pairs = array();
    foreach ($params as $k => $v) {
        $pairs[] = $k . '=' . urlencode($v);
    }

    $prefix = IA_URL_PREFIX;

    if (0 < count($pairs)) {
        return $prefix . $document . '?' . join('&amp;', $pairs);
    }
    else {
        return $prefix . $document;
    }
}


// Use flash() to display a message right after redirecting the user.
// Message is displayed only once.
function flash($message, $styleClass = null) {
    global $_SESSION;
    $_SESSION['_flash'] = $message;
    if (!is_null($styleClass)) {
        $_SESSION['_flash_class'] = $styleClass;
    }
}

// This is a simple binding for flash() with a fixed CSS style class
// for displaying error messages
function flash_error($message) {
    flash($message, 'flashError');
}

// Execute a view. Variables in $view are placed in the
// local namespace as variables. This is the preffered
// way of calling a template, because globals are not
// easily accessible.
function execute_view($view_file_name, $view) {
    global $identity_user;

    foreach ($view as $view_hash_key => $view_hash_value) {
        if ($view_hash_key == 'view_hash_key') continue;
        if ($view_hash_key == 'view_hash_value') continue;
        if ($view_hash_key == 'view_file_name') continue;
        if ($view_hash_key == 'view') continue;
        $$view_hash_key = $view_hash_value;
    }
/*    foreach ($GLOBALS as $the_key => $the_value) {
        // Don't unset magic shit.
        if ($the_key[0] != '_' && strpos($the_key, 'HTTP_') !== 0) {
            unset($GLOBALS[$the_key]);
            echo "Am sters $the_key";
        }
    }*/
    include('views/utilities.php');
    include($view_file_name);
    //include('views/vardump.php');
}

// Execute and the die.
function execute_view_die($view_file_name, $view)
{
    execute_view($view_file_name, $view);
    die();
}

?>
