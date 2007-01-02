<?php

require_once(IA_ROOT.'www/format/format.php');

// Validate field information
// Form information is an array with the following:
//
//      'type': integer, float, string, bool, enum, set
// Enum and set also require a set of possible values. The different
// between them is that set can take multiple values. Bool looks *very*
// similar to an enum.
//      'default': A default value.
//      'name': Pretty display name for the field.
//      'description': Pretty helpful text about the field.
function fieldinfo_validate($finfo) {
    log_assert(is_array($finfo), 'Arrays only');
    $errors = array();

    // Default value absolutely required.
    if (!array_key_exists('name', $finfo)) {
        $errors['name'] = 'Field name is not optional';
    }

    // Default value absolutely required.
    if (!array_key_exists('default', $finfo)) {
        $errors['default'] = 'Default value missing';
    }

    // Check type is good.
    $type = getattr($finfo, 'type');
    static $types = array('integer', 'float', 'string',
            'bool', 'enum', 'set');
    if (!in_array($type, $types)) {
        $errors['type'] = "Invalid field type '$type'";
    }

    if (!array_key_exists('type', $errors)) {
        if ($type == 'enum' || $type == 'set') {
            $values = getattr($info, 'values');
            if (!is_array($values)) {
                $errors['values'] = 'Need to specify possible values';
            } else {
                foreach ($values as $val) {
                    if (is_string($val) == false || htmlentities($val) != $val) {
                        $errors['values'] = "Bad value '$val'";
                    }
                }
            }
        }
    }

    // FIXME: Validate default value

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
        $field_value = $field_info['default'];
    }

    if ($type == 'integer' || $type == 'string' || $type == 'float') {
        return format_tag('input', null, array(
                'type' => 'text',
                'name' => $field_name,
                'id' => "form_$field_name",
                'value' => htmlentities($field_value),
        ));
    } else if ($type == 'bool' || $type == 'enum' || $type == 'set') {
        if ($type == 'bool') {
            $values = array('0' => 'Nu', '1' => 'Da');
        } else {
            $values = $finfo['values'];
        }
        $select_attribs = array();
        $select_attribs['name'] = $field_name;
        $select_attribs['id'] = "form_$field_name";
        if ($type == 'set') {
            log_assert(is_array($fval), 'Set fields have arrays as values');
            $select_attribs['multiple'] = 'multiple';
            $select_attribs['size'] = 10;
            $select_attribs['name'] .= '[]';
        } else {
            // How evil am I?
            $field_value = array($field_value);
        }
        $res = format_open_tag('select', $select_attribs);
        foreach ($values as $val => $content) {
            $option_attribs = array();
            $option_attribs['value'] = $val;
            if (in_array($val, $field_value)) {
                $option_attribs['selected'] = 'selected';
            }
            $res .= format_tag('option', $content, $option_attribs);
        }
        $res .= '</select>';
        return $res;
    } else {
        log_error("Unknown type '$type'");
    }
}

// Format a certain form field. Returns something like
// <label> <input> or <select> <error span> <info span>
function format_form_field($field_info, $field_name,
        $field_value = null, $field_error = null, $enclose_in_tds = false) {
    $label = "<label for=\"form_$field_name\">{$field_info['name']}</label>";
    if ($field_error != null) {
        $errspan = "<span class=\"fieldError\">$field_error</span>";
    } else {
        $errspan = '';
    }

    $editor = format_form_field_inner_editor($field_info, $field_name, $field_value);
    if (array_key_exists('description', $field_info)) {
        $helpspan = '<span class="fieldHelp">'.$field_info['description'].'</span>';
    } else {
        $helpspan = '';
    }

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
        ));
        foreach ($field_infos as $name => $field_info) {
            $fname = "param_{$type}_{$name}";
            $row = format_form_field($field_info, $fname,
                    getattr($form_values, $fname),
                    getattr($form_errors, $fname), false);
            $res .= "<li id=\"field_$fname\">\n$row</li>\n";
        }
        $res .= "</ul>\n";
    }
    return $res;
}

?>
