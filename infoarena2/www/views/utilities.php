<?php

function fval($paramName) {
    global $view;

    return htmlentities(getattr($view['form_values'], $paramName));
}

function ferr_span($paramName) {
    $error = ferr($paramName);

    if ($error) {
        return '<span class="fieldError">' . $error . '</span>';
    }
    else {
        return null;
    }
}

function ferr($paramName) {
    global $view;
    return htmlentities(getattr($view['form_errors'], $paramName));
}

?>
