<?php

require_once(IA_ROOT_DIR.'www/format/format.php');

// Validate field information
// Form information is an array with the following:
//
//      'type': integer, float, string, bool, enum, set, datetime
// Enum and set also require a set of possible values. The different
// between them is that set can take multiple values. Bool looks *very*
// similar to an enum.
//      'default': A default value.
//      'name': Pretty display name for the field.
//      'description': Pretty helpful text about the field.
function fieldinfo_validate($finfo) {
    log_assert(is_array($finfo), 'Arrays only');
    $errors = array();

    // Check name
    if (!array_key_exists('name', $finfo) || !is_string($finfo['name'])) {
        $errors['name'] = 'Field name is not optional';
    }

    // Check type is good.
    $type = getattr($finfo, 'type');
    static $types = array('integer', 'float', 'string',
            'bool', 'enum', 'set', 'datetime');
    if (!in_array($type, $types)) {
        $errors['type'] = "Invalid field type '$type'";
    }

    if (!array_key_exists('type', $errors)) {
        if ($type == 'enum' || $type == 'set') {
            $values = getattr($finfo, 'values');
            if (!is_array($values)) {
                $errors['values'] = 'Need to specify possible values';
            } else {
                foreach ($values as $key => $val) {
                    if (html_escape($key) != $key) {
                        $errors['values'] = "Bad value '$key'";
                    }
                }
            }
            if ($type == 'enum') {
                if (!array_key_exists(getattr($finfo, 'default'), $values)) {
                    $errors['default'] = "Default value missing among valid values.";
                }
            } else if ($type == 'set') {
                foreach (getattr($finfo, 'default', array()) as $dv) {
                    if (!array_key_exists($dv, $values)) {
                        $errors['default'] = "Bad defaults";
                    }
                }
            }
        } elseif ($type == 'bool') {
            if (array_key_exists('values', $finfo)) {
                $errors['values'] = "Can't have values for bool type.";
            }
        } else if ($type == 'integer') {
            if (!is_whole_number(getattr($finfo, 'values', 0))) {
                $errors['default'] = "Must be an integer";
            }
        } else if ($type == 'float') {
            if (!is_numeric(getattr($finfo, 'values', 0))) {
                $errors['default'] = "Must be an number";
            }
        }
    }

    return $errors;
}

// Format a certain form field's editor.
// $field_value defaults on $field_info['default']
//
// This returns an <input> or <select> tag.
function format_form_field_inner_editor(
        $field_info, $field_name, $field_value = null) {
    log_assert_valid(fieldinfo_validate($field_info));
    $type = $field_info['type'];
    if ($field_value === null) {
        $field_value = getattr($field_info, 'default');
    }

    if ($type == 'integer' ||
        $type == 'string' ||
        $type == 'float' ||
        $type == 'datetime') {
        $input_type = 'text';
        if (getattr($field_info, 'is_password', false)) {
            $input_type = 'password';
        }
        return format_tag('input', null, array(
                'type' => $input_type,
                'name' => $field_name,
                'id' => "form_$field_name",
                'value' => $field_value,
        ));
    } else if ($type == 'bool' || $type == 'enum' || $type == 'set') {
        // All these are handled as a select.
        // Set uses a smart selctor with two lists.

        if ($type == 'bool') {
            // Convert bools to enums.
            $values = array(
                    '0' => 'Nu',
                    '1' => 'Da'
            );
        } else {
            $values = $field_info['values'];
        }

        // Attributes for the select tag.
        $select_attribs = array();
        $select_attribs['name'] = $field_name;
        $select_attribs['id'] = "form_$field_name";
        if ($type == 'set') {
            log_assert(is_array($field_value), 'Set fields have arrays as values');
            $select_attribs['multiple'] = 'multiple';
            $select_attribs['size'] = 10;
            $select_attribs['name'] .= '[]';
        } else {
            // field_value contains the selected value, if not a set
            // then we pack it in an array of one element.
            // How evil am I?
            $field_value = array($field_value);
        }

        // Generate html
        $res = format_select_box($values, $field_value, $select_attribs);
        return $res;
    }
}

// Format SELECT tag
function format_select_box($options, $selected_options = array(), $tag_attribs = array()) {
    $html_buffer = format_open_tag('select', $tag_attribs);
    foreach ($options as $val => $content) {
        $option_attribs = array('value' => $val);
        if (in_array($val, $selected_options)) {
            $option_attribs['selected'] = 'selected';
        }
        $html_buffer .= format_tag('option', $content, $option_attribs);
    }
    $html_buffer .= '</select>';

    return $html_buffer;
}

// Format a certain form field. Returns something like
// <label> <error span> <editor> <info span>
function format_form_field($field_info, $field_name,
        $field_value = null, $field_error = null, $enclose_in_tds = false) {

    // Format label.
    $label = "<label for=\"form_$field_name\"";
    if (array_key_exists(' access_key', $field_info)) {
        $label .= 'accesskey="';
        $label .= $field_info['access_key'];
        $label .= '">';
        $label .= format_highlight_access_key(
                $field_info['name'], $field_info['access_key']);
    } else {
        $label .= '>';
        $label .= $field_info['name'];
    }
    $label .= '</label>';

    // Format error span.
    if ($field_error != null) {
        $errspan = "<span class=\"fieldError\">$field_error</span>";
    } else {
        $errspan = '';
    }

    // Format editor.
    $editor = format_form_field_inner_editor($field_info, $field_name, $field_value);
    if (array_key_exists('description', $field_info)) {
        $helpspan = '<span class="fieldHelp">'.$field_info['description'].'</span>';
    } else {
        $helpspan = '';
    }

    // Enclose in tds and return.
    if ($enclose_in_tds) {
        return "<td>$label</td>\n<td>$editor$errspan</td>\n<td>$helpspan</td>\n";
    } else {
        return "$label\n$errspan\n$editor\n$helpspan";
    }
}

// Format a parameter editor as a table
function format_param_editor_table($param_infos, $form_values, $form_errors) {
    $res = '';
    foreach ($param_infos as $type => $field_infos) {
        $res .= format_open_tag('table', array(
                'class' => 'parameters',
                'id' => "params_$type",
        ));
        $res .= '<thead><tr>';
        $res .= "<th>Parametru</th><th>Valoare</th><th>Descriere</th></tr>\n";
        $res .= "</thead><tbody>\n";
        foreach ($field_infos as $name => $field_info) {
            $fname = "param_{$type}_{$name}";
            $row = format_form_field($field_info, $fname,
                    getattr($form_values, $fname),
                    getattr($form_errors, $fname), true);
            $res .= "<tr>$row</tr>\n";
        }
        $res .= "</tbody></table>\n";
    }
    return $res;
}

// Format a parameter editor as a list
function format_param_editor_list($param_infos, $form_values, $form_errors) {
    $res = '';
    foreach ($param_infos as $type => $field_infos) {
        $res .= format_open_tag('ul', array(
                'class' => 'form parameters',
                'id' => "params_$type",
        ))."\n";
        if (count($field_infos) == 0) {
            // Horrible horrible hack to validate xhtml.
            $res .= <<<HTML
<li><span class="fieldHelp">Nu există nici un parametru configurabil.</span></li>
HTML;
        } else {
            foreach ($field_infos as $name => $field_info) {
                $fname = "param_{$type}_{$name}";
                $row = format_form_field($field_info, $fname,
                        getattr($form_values, $fname),
                        getattr($form_errors, $fname), false);
                $res .= "<li id=\"field_$fname\">\n$row</li>\n";
            }
        }
        $res .= "</ul>\n";
    }
    return $res;
}

// Format a radio input button
function format_radio_button($attribs) {
    $args = array('type' => 'radio') + $attribs;
    return format_tag('input', null, $args);
}

?>
