<?php

require_once("utilities.php");

// This builds a bunch of default $column_infos for format_table.
// See format_table for an explanation.
function build_default_column_infos($data)
{
    if (count($data) < 1) {
        return false;
    }
    $infos = array();
    foreach ($data[1] as $key => $value)  {
        $infos[] = array('name' => $key, 'key' => $key,);
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
//      row_formatter: An optional callable(is_callable) which can be used to
//          format the data before being placed in the table.
//          The data row and key are sent as parameters to this function.
//      val_formatter: An optional callable(is_callable) which can be used to
//          format the data before being placed in the table.
//          The data value is sent as a parameter.
// val_formater and row_formatter are mutually exclusive.
//
// $options is an array of options (you can skip any of them):
//      skip_header: Will skip the header.
//      css_class: The css class for the table tag.
//      css_row_parity: Adds class=even and class=odd for table rows.
//          Defaults to true!
//            
// TODO: pagination.
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

    // Table header: Column names.
    if (!getattr($options, 'skip_header', false)) {
        $result .= "<thead><tr>";
        foreach ($column_infos as $column) {
            assert(isset($column['title']));
            $result .= "<th>" . $column['title'] . "</th>";
        }
        $result .= "</tr></thead>";
    }

    // Table body: data
    $result .= "<tbody>";
    for ($i = 0; $i < count($data); ++$i) {
        $row = $data[$i];

        // Odd/even rows.
        if (getattr($options, 'css_row_parity', true)) {
            $result .= "<tr class='" . ($i % 2 ? 'odd' : 'even') . "'>";
        } else {
            $result .= "<tr>";
        }

        // Dump the actual data.
        foreach ($column_infos as $column) {
            assert(isset($column['key']));
            $key = $column['key'];
            assert(isset($row[$key]));
            $val = $row[$key];

            // Handle row formatter.
            if (isset($column['row_formatter'])) {
                assert(!isset($column['val_formatter']));
                assert(is_callable($column['row_formatter']));
                $val = $column['row_formatter']($row, $key);
            }

            // Handle val formatter
            if (isset($column['val_formatter'])) {
                assert(!isset($column['row_formatter']));
                assert(is_callable($column['val_formatter']));
                $val = $column['val_formatter']($val);
            }

            $result .= "<td>$val</td>";
        }

        $result .= '</tr>';
    }
    $result .= "</tbody>";

    // FIXME: Table footer: pagination.

    $result .= "</table>";
    return $result;
}

?>
