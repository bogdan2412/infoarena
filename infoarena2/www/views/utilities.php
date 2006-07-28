<?php

function fval($paramName) {
    global $view;
    return htmlentities(getattr($view['form_values'], $paramName));
}

function ferr($paramName) {
    global $view;
    return htmlentities(getattr($view['form_errors'], $paramName));
}

?>
