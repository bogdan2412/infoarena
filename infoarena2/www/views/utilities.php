<?php

function fval($paramName) {
    global $view;
    return htmlentities(getattr($view['form_values'], $paramName));
}

function ferr_span($paramName) {
    global $error;
    if (isset($error[$paramName])) {
        echo '<span class="fieldError">' . htmlentities($error[$paramName])
             . '</span>';

        return htmlentities($error[$paramName]);
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
