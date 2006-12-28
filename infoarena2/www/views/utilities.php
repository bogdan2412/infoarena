<?php

require_once(IA_ROOT.'www/wiki/wiki.php');
require_once(IA_ROOT.'common/db/textblock.php');
require_once(IA_ROOT.'www/format/format.php');

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

// fval(...) for checkboxes
// returns ' checked="checked"' when parameter value is true
// returns '' (blank string) otherwise
function fval_checkbox($param_name) {
    global $view;

    if (!isset($view['form_values'])) {
        return '';
    }

    if (getattr($view['form_values'], $param_name)) {
        return ' checked="checked"';
    }
    else {
        return '';
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

// Parse and print a textblock. Use this to insert dynamic textblocks
// inside static templates / views.
// FIXME: printing from functions is sort of retarded.
function wiki_include($page_name, $template_args = null) {
    $textblock = textblock_get_revision($page_name);
    log_assert($textblock, "Nu am gasit $page_name");

    if (!is_null($template_args)) {
        log_print("Replacing stuff in $page_name");
        textblock_template_replace($textblock, $template_args);
    }

    echo '<div class="wiki_text_block">';
    echo wiki_process_text($textblock['text']);
    echo '</div>';
}

// Format a simple form input tag.
function format_form_text_input($field) {
    return format_tag('input', null, array(
            'type' => 'text',
            'name' => $field,
            'id' => 'form_' . $field,
            'value' => fval($field, false),

    ));
}

// Formats a simple form text field
function format_form_text_field($field, $info) {
    $res = '';
    $res .= format_tag('label', $info, array(
            'for' => 'form_' . $field,
    ));
    $res .= ferr_span($field);
    $res .= format_form_text_input($field);
    return $res;
}

?>
