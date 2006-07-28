<?php

function fval($paramName) {
    global $view;
    return htmlentities(getattr($view['form_values'], $paramName));
}

function ferr_span($paramName) {
    $error = ferr($paramName);

    if ($error) {
        echo '<span class="fieldError">' . $error . '</span>';
        return $error;
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
