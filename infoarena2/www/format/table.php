<?php

require_once(IA_ROOT."www/utilities.php");
require_once(IA_ROOT."www/format/pager.php");
require_once(IA_ROOT."www/format/format.php");

// This builds a bunch of default $column_infos for format_table.
// See format_table for an explanation.
function build_default_column_infos($data)
{
    if (count($data) < 1) {
        return false;
    }
    $infos = array();
    foreach ($data[1] as $key => $value)  {
        $infos[] = array(
                'title' => $key,
                'key' => $key,
        );
    }
    return $infos;
}

// This function formats data into a table.
//
// $data contains the actual data as an array of arrays.
//
// $column_infos is an array of column informations.
// For each column you must provide the following info:
//      title: The pretty name to be displayed in the table header.
//      key: The key from the data table to display. Cell $i, $j will contain
//          $data[$i][column_infos[$j]['key']].
//      rowform: An optional callback(is_callable) which can be used to
//          format the data before being placed in the table.
//          The data row and key are sent as parameters to this function.
//          If this is present they key is optional.
//      valform: An optional callback(is_callable) which can be used to
//          format the data before being placed in the table.
//          The data value is sent as a parameter.
//      dateform: Optional format for timestamps. date() function format.
// The various *form fields are mutually exclusive.
//
// $options is an array of options (you can skip any of them):
//      skip_header: Will skip the header.
//      css_class: The css class for the table tag.
//      css_row_parity: Adds class=even and class=odd for table rows.
//          Defaults to true!
//
// Additionally you can merge a pager_options, and it will display a
// paging table footer.
//
// FIXME: sorting :)
function format_table($data, $column_infos = null, $options = null)
{
    // No data means nothing to print.
    if (count($data) < 1) {
        return false;
    }

    // Table starting tag.
    if (isset($options['css_class'])) {
        $result = "<table class='" . $options['css_class'] . "'>";
    } else {
        $result = "<table>";
    }

    // Handle missing column infos.
    if ($column_infos == null) {
        $column_infos = build_default_column_infos($data);
    }

    // Table header: Column names.
    if (!getattr($options, 'skip_header', false)) {
        $result .= "<thead><tr>";
        foreach ($column_infos as $column) {
            $args = array();
            if (isset($column['css_class'])) {
                $args['class'] = $column['css_class'];
            }
            if (isset($column['css_style'])) {
                $args['style'] = $column['css_style'];
            }
            $result .= format_tag('th', $column['title'], $args);
        }
        $result .= "</tr></thead>";
    }

    // Check for multipler formers.
    foreach ($column_infos as $column) {
        log_assert(isset($column['valform']) + 
               isset($column['rowform']) + 
               isset($column['dateform']) <= 1,
               "Column info can't have multiple format functions");
    }

    // Table body: data
    $result .= "<tbody>";
    for ($i = 0; $i < count($data); ++$i) {
        $row = $data[$i];

        // row style class (you can use both row_style & css_row_parity)
        $func = getattr($options, 'row_style', null);
        $class = '';
        if ($func && is_callable($func)) {
            $class .= $func($row);
        }
        if (getattr($options, 'css_row_parity', true)) {
            if ($class) {
                $class .= ' ';
            }
            $class .= ($i % 2 ? 'even' : 'odd');
        }

        if (!$class) {
            $result .= '<tr>';
        }
        else {
            $result .= '<tr class="'.htmlentities($class).'">';
        }

        // Dump the actual data.
        foreach ($column_infos as $column) {
            // Handle row formatter.
            if (isset($column['rowform'])) {
                log_assert(is_callable($column['rowform']));
                if (key_exists('key', $column)) {
                    $val = $column['rowform']($row, $column['key']);
                } else {
                    $val = $column['rowform']($row);
                }
            } else {
                $key = $column['key'];
                $val = $row[$key];

                // Handle val formatter.
                if (isset($column['valform'])) {
                    log_assert(is_callable($column['valform']));
                    $val = $column['valform']($val);
                // Handle date formatter.
                } else if (isset($column['dateform'])) {
                    // log_assert(is_timestamp($val));
                    $val = date($column['dateform'], $val);
                }
            }

            $args = array();
            if (isset($column['css_class'])) {
                $args['class'] = $column['css_class'];
            }
            if (isset($column['css_style'])) {
                $args['style'] = $column['css_style'];
            }
            $result .= format_tag('td', $val, $args, false);
        }
        $result .= '</tr>';
    }

    $result .= "</tbody>";

    $result .= "</table>";

    // Paging.
    if (getattr($options, 'pager_style', 'none') != 'none') {
        $result .= format_pager($options);
    }

    return $result;
}

?>
