<?php

require_once(IA_ROOT_DIR.'www/wiki/wiki.php');
require_once(IA_ROOT_DIR.'common/db/textblock.php');
require_once(IA_ROOT_DIR.'www/format/format.php');

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
function wiki_include($page_name, $template_args = null, $div = true) {
    $textblock = textblock_get_revision($page_name);
    log_assert($textblock, "Nu am gasit $page_name");

    if ($div) {
        echo '<div class="wiki_text_block">';
    }
    if (!is_null($template_args)) {
        textblock_template_replace($textblock, $template_args);
        // No caching, we're using template magic.
        echo wiki_process_text($textblock['text']);
    } else {
        echo wiki_process_textblock($textblock);
    }
    if ($div) {
        echo '</div>';
    }
}

// Format a field as a li. Uses global form_values/errors.
function view_form_field_li($field_info, $field_name) {
    require_once(IA_ROOT_DIR.'www/format/form.php');
    global $form_values, $form_errors;

    $row = format_form_field($field_info, $field_name,
            getattr($form_values, $field_name),
            getattr($form_errors, $field_name), false);
    return "<li id=\"field_$field_name\">\n$row</li>\n";
}

// Format a field as a tr. Uses global form_values/errors.
function view_form_field_tr($field_info, $field_name) {
    require_once(IA_ROOT_DIR.'www/format/form.php');
    global $form_values, $form_errors;

    $row = format_form_field($field_info, $field_name,
            getattr($form_values, $field_name),
            getattr($form_errors, $field_name), true);
    $return .= "<tr id=\"field_$field_name\">\n$row</tr>\n";
}

?>
