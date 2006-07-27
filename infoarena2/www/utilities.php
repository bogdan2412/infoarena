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

/**
 * Use flash() to display a message right after redirecting the user.
 * Message is displayed only once.
 */
function flash($message) {
    global $_SESSION;
    $_SESSION['_flash'] = $message;
}

?>
