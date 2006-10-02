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
        $infos[] = array(
                'title' => $key,
                'key' => $key,
        );
    }
    return $infos;
}

// Checks if the string is a valid page style.
// standard and none right now.
function valid_pager_style($style)
{
    return $style == 'none' || $style == 'standard';
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
//      first_row: First displayed entry, usefull in pagination
//      total_rows: Total number of entries.
//      display_rows: Number of entries displayed at once.
//      url_args: url arguments (page is an argument too).
//      param_prefix: optional parameter prefix for browsing links.
//      pager_style: none(default) or standard.
//
// url_args is only required if you want browsing links, thing like paging
// and sorting. Same restrictions as for the url_from_args function.
// When browsing links are used then some parameters (with an optional
// param_prefix) are added to the url_args.
// If absert, url_args defaults to $_GET.
//
// Paging is done with the 'start' and 'count' parameters.
//
// TODO: paging, sorting.
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
            $title = log_assert_getattr($column, 'title');
            $result .= "<th>" . $title . "</th>";
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

        // Odd/even rows.
        if (getattr($options, 'css_row_parity', true)) {
            $result .= "<tr class='" . ($i % 2 ? 'even' : 'odd') . "'>";
        } else {
            $result .= "<tr>";
        }

        // Dump the actual data.
        foreach ($column_infos as $column) {
            // Handle row formatter.
            if (isset($column['rowform'])) {
                log_assert_is_callable($column['rowform']);
                if (key_exists('key', $column)) {
                    $val = $column['rowform']($row, $column['key']);
                } else {
                    $val = $column['rowform']($row);
                }
            } else {
                $key = log_assert_getattr($column, 'key');
                $val = log_assert_getattr($row, $key);

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

            $result .= "<td>$val</td>";
        }
        $result .= '</tr>';
    }
    $result .= "</tbody>";

    // Handle pager.
    $pager_style = getattr($options, 'pager_style', 'none');
    if (!valid_pager_style($pager_style)) {
        log_die("Unknown pager style $pager_style.");
    }
    //log_print("pager style $pager_style");
    if ($pager_style == 'standard') {
        $result .= '<tfoot style="standard-pager"><tr><td colspan="0">';
        $result .= format_standard_pager($options);
        $result .= '</td></tr></tfoot>';
    }

    $result .= "</table>";
    return $result;
}

// formats a standard pager. Used by format_table.
function format_standard_pager($options)
{
    $first_row = log_assert_getattr($options, 'first_row', 0);
    $total_rows = log_assert_getattr($options, 'total_rows');
    $display_rows = log_assert_getattr($options, 'display_rows', IA_DEFAULT_ROWS_PER_PAGE);
    $url_args = getattr($options, 'url_args', $_GET);
    $param_prefix = getattr($options, 'param_prefix', '');
    $surround_pages = getattr($options, 'surround_pages', 2);

    $result = "";

    $curpage = (int)($first_row / $display_rows);
    $totpages = (int)(($total_rows + $display_rows - 1) / $display_rows);

    if ($totpages == 1) {
        return "Exista o singura pagina.";
    }
    $result .= "Vezi pagina ".($curpage + 1)." din $totpages: ";
    if ($curpage < 8) {
        for ($i = 0; $i < $curpage; ++$i) {
            $url_args[$param_prefix.'start'] = $i * $display_rows;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    } else {
        for ($i = 0; $i < $surround_pages; ++$i) {
            $url_args[$param_prefix.'start'] = $i * $display_rows;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
        $result .= "... ";
        for ($i = $curpage - $surround_pages; $i < $curpage; ++$i) {
            $url_args[$param_prefix.'start'] = $i * $display_rows;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    }
    $result .= ($curpage + 1)." ";
    if ($totpages - $curpage < 3 + 2 * $surround_pages) {
        for ($i = $curpage + 1; $i < $totpages; ++$i) {
            $url_args[$param_prefix.'start'] = $i * $display_rows;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    } else {
        for ($i = $curpage + 1; $i <= $curpage + $surround_pages; ++$i) {
            $url_args[$param_prefix.'start'] = $i * $display_rows;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
        $result .= "... ";
        for ($i = $totpages - $surround_pages; $i < $totpages; ++$i) {
            $url_args[$param_prefix.'start'] = $i * $display_rows;
            $result .= href(url_from_args($url_args), $i + 1)." ";
        }
    }

    return $result;
}

?>
