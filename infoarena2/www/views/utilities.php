<?php

// Check the big view variable for consistency.
function check_view($view)
{
    // Checking $view.
    log_assert(is_array($view));
    log_assert(is_string($view['title']));
    if (isset($view['form_errors']) || isset($view['form_values'])) {
        log_assert(is_array($view['form_errors']));
        log_assert(is_array($view['form_values']));
    }
    log_assert(!isset($view['wikipage']));
    if (isset($view['textblock'])) {
        log_assert(is_string($view['page_name']));
        log_assert(is_array($view['textblock']));
        log_assert(array_key_exists('name', $view['textblock']));
        log_assert(array_key_exists('title', $view['textblock']));
        log_assert(array_key_exists('text', $view['textblock']));
        log_assert(array_key_exists('timestamp', $view['textblock']));
    }
    if (isset($view['task'])) {
        log_assert(is_array($view['task']));
        log_assert(is_array($view['task_parameters']));
        //.. more here.
    }
}

function fval($paramName, $escapeHtml = true) {
    global $view;

    if ($escapeHtml) {
        return htmlentities(getattr($view['form_values'], $paramName));
    }
    else {
        return getattr($view['form_values'], $paramName);
    }
}

function ferr_span($paramName, $escapeHtml = true) {
    $error = ferr($paramName, $escapeHtml);

    if ($error) {
        return '<span class="fieldError">' . $error . '</span>';
    }
    else {
        return null;
    }
}

function ferr($paramName, $escapeHtml = true) {
    global $view;

    if ($escapeHtml) {
        return htmlentities(getattr($view['form_errors'], $paramName));
    }
    else {
        return getattr($view['form_errors'], $paramName);
    }
}

?>
