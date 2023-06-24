<?php

require_once(IA_ROOT_DIR . 'common/db/textblock.php');
require_once(IA_ROOT_DIR . 'lib/Wiki.php');
require_once(IA_ROOT_DIR . 'www/format/format.php');
require_once(IA_ROOT_DIR . 'www/wiki/wiki.php');

// returns a form value, html-escaped by default.
function fval($param_name, $escape_html = true) {
    global $view;

    if (!isset($view['form_values'])) {
        return '';
    }

    if ($escape_html) {
        return html_escape(getattr($view['form_values'], $param_name));
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

    if (!isset($view["form_errors"])) {
        return null;
    }

    $error = getattr($view['form_errors'], $param_name);
    if (!is_array($error)) {
        $error = array($error);
    }

    if ($escape_html) {
        foreach ($error as &$message) {
            $message = html_escape($message);
        }
    }

    return implode("<br>", $error);
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
    $return = "";
    $return .= "<tr id=\"field_$field_name\">\n$row</tr>\n";
    return $return;
}

?>
