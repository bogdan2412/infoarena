<?php

// Check the big view variable for consistency.
function check_view($view)
{
    assert(is_array($view));
    assert(is_string($view['title']));
    if (isset($view['form_errors']) || isset($view['form_values'])) {
        assert(is_array($view['form_errors']));
        assert(is_array($view['form_values']));
    }
    assert(!isset($view['wikipage']));
    if (isset($view['textblock'])) {
        assert(is_array($view['textblock']));
        assert(is_array($view['textblock_context']));
        check_context($view['textblock_context']);
        assert(isset($view['textblock']['name']));
        assert(isset($view['textblock']['title']));
        assert(isset($view['textblock']['text']));
        assert(isset($view['textblock']['timestamp']));
    }
    if (isset($view['task'])) {
        assert(is_array($view['task']));
        assert(is_array($view['task_parameters']));
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
