<?php

// Check the big view variable for consistency.
function check_view($view)
{
    log_assert_is_array($view);
    log_assert(is_string($view['title']));
    //log_assert_getattr($view, 'url_page');
    //log_assert(is_string($view['url_page']), "url_page missing");
    //log_assert(isset($view['url_args']) == false ||
    //        is_array($view['url_args']), "url_args must be an array");
    if (isset($view['form_errors']) || isset($view['form_values'])) {
        log_assert_is_array($view['form_errors']);
        log_assert_is_array($view['form_values']);
    }
    log_assert(!isset($view['wikipage']));
    if (isset($view['textblock'])) {
        log_assert_is_array($view['textblock']);
        log_assert_is_array($view['textblock_context']);
        check_context($view['textblock_context']);
        log_assert_getattr($view['textblock'], 'name');
        log_assert_getattr($view['textblock'], 'title');
        log_assert_getattr($view['textblock'], 'text');
        log_assert_getattr($view['textblock'], 'timestamp');
    }
    if (isset($view['task'])) {
        log_assert_is_array($view['task']);
        log_assert_is_array($view['task_parameters']);
        //.. more here.
    }

    //var_dump($view);
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
