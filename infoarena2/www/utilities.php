<?php

function request($paramName, $defaultValue = null) {
    return getattr($_REQUEST, $paramName, $defaultValue);
}

function getattr($dict, $attribute, $defaultValue = null) {
    if (isset($dict[$attribute])) {
        return $dict[$attribute];
    }
    else {
        return $defaultValue;
    }
}

function redirect($absoluteUrl) {
    header("Location: {$absoluteUrl}\n\n");
    die();
}

function url($document, $params = array()) {
    assert(false === strpos($document, '?'));

    if (!IA_URL_REWRITE) {
        $params['page'] = $document;
        $document = 'index.php';
    }

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

?>
