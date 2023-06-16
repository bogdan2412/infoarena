<?php

require_once(IA_ROOT_DIR.'common/dataset.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");

function format_dataset_filters($dataset, $view_options = array()) {
    $filters = $dataset->availableFilters();

    if (0 == count($filters)) {
        return '';
    }

    return '<div class="dataset-filters">'
           .format_filter_form($dataset, $view_options)
           .'</div>';
}

function format_filter_form($dataset, $options = array()) {
    global $FILTER_GRAMMAR;

    $filters = $dataset->availableFilters();
    $active_filters = $dataset->activeFilters();

    $filter_op_captions = array('eq' => '==', 'lt' => '<', 'gt' => '>',
                                'like' => 'începe cu', 'contains' => 'conține');

    $form_class = "filters ".getattr($options, 'class', '');
    $html_buffer = format_open_tag('form', array('action' => '',
                                                 'method' => getattr($options, 'method', 'post'),
                                                 'id' => getattr($options, 'id'),
                                                 'class' => $form_class));
    $html_buffer .= format_open_tag('ul', array('class' => 'filters'));

    foreach ($filters as $field_id => $field) {
        $html_field_id = getattr($options, 'prefix', 'filter_').$field_id;
        $html_op_field_id = getattr($options, 'prefix', 'filter_').'op_'.$field_id;
        $html_buffer .= format_open_tag('li', array('class' => 'filter'));

        // active filter data
        // FIXME: Dirty hack reads values from request. It should read'em from dataset
        $active_filter_op = request($html_op_field_id);
        $active_filter_value = request($html_field_id);

        // label
        $html_buffer .= format_tag('label', $field['name'], array('for' => $html_field_id));

        // operator
        $op_options = array();
        foreach ($FILTER_GRAMMAR[$field['type']] as $filter_type) {
            $op_options[$filter_type] = $filter_op_captions[$filter_type];
        }
        $html_buffer .= format_select_box($op_options, array($active_filter_op),
                                          array('id' => $html_op_field_id,
                                                'name' => $html_op_field_id,
                                                'class' => 'op'));

        // value
        // FIXME: Display different kinds of HTML UI for every filter type / operator
        $html_buffer .= format_tag("input", null, array("name" => $html_field_id,
                                                        "id" => $html_field_id,
                                                        "type" => "text",
                                                        "value" => $active_filter_value));
        /*
        switch ($field['type']) {
        default:
            // log_error('Unknown field type: '.$field['type']);
            break;
        }
        */
        $html_buffer .= "</li>\n";
    }

    // submit
    $html_buffer .= format_open_tag('li', array('class' => 'filter submit'));
    $html_buffer .= format_tag('input', null, array('type' => 'submit', 'value' => 'Trimite'));
    $html_buffer .= '</li>';

    $html_buffer .= '</ul>';
    $html_buffer .= "</form>\n";

    return $html_buffer;
}

// Parse request params and issue filters for $dataset
function filters_parse_request($dataset, &$view_options) {
    log_assert($dataset instanceof Filterable);
    $filters = $dataset->availableFilters();
    foreach ($filters as $field_id => $field) {
        $html_field_id = getattr($view_options, 'prefix', 'filter_').$field_id;
        $html_op_field_id = getattr($view_options, 'prefix', 'filter_').'op_'.$field_id;

        $filter_op = request($html_op_field_id);
        $filter_value = request($html_field_id);

        if (!$filter_value) {
            continue;
        }

        // FIXME: validation
        $dataset->addFilter($field_id, $filter_op, $filter_value);
    }
}

?>
