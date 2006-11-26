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
    if (isset($view['textblock'])) {
        require_once(IA_ROOT . "common/textblock.php");
        log_assert(is_string($view['page_name']));
        log_assert_valid(textblock_validate($view['textblock']));
    }
    if (isset($view['task'])) {
        log_assert(is_array($view['task']));
        log_assert(is_array($view['task_parameters']));
        //.. more here.
    }
}

// returns a form value, html-escaped by default.
function fval($param_name, $escape_html = true) {
    global $view;

    if (!isset($view['form_values'])) {
        return '';
    }

    if ($escape_html) {
        return htmlentities(getattr($view['form_values'], $param_name));
    } else {
        return getattr($view['form_values'], $param_name);
    }
}

// returns a form error, html-escaped by default.
function ferr($param_name, $escape_html = true) {
    global $view;

    if ($escape_html) {
        return htmlentities(getattr($view['form_errors'], $param_name));
    } else {
        return getattr($view['form_errors'], $param_name);
    }
}

// returns a form error span, html-escaped by default.
function ferr_span($param_name, $escape_html = true) {
    $error = ferr($param_name, $escape_html);

    if ($error) {
        return '<span class="fieldError">' . $error . '</span>';
    } else {
        return null;
    }
}

?>
