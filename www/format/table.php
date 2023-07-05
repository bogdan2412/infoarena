<?php

require_once(IA_ROOT_DIR."www/utilities.php");
require_once(IA_ROOT_DIR."www/format/pager.php");
require_once(IA_ROOT_DIR."www/format/format.php");

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
//      css_class: The css class for the table tag.
//
// Additionally you can merge a pager_options, and it will display a
// paging table footer.
function format_table($data, $column_infos = null, $options = null)
{
  // No data means nothing to print.
  if (count($data) < 1) {
    return false;
  }

  $result = "";
  // Paging.
  if (getattr($options, 'pager_style', 'none') != 'none') {
    $result .= format_pager($options);
  }

  // Table starting tag.
  if (isset($options['css_class'])) {
    $result .= "<table class='" . $options['css_class'] . "'>";
  } else {
    $result .= "<table>";
  }

  // Handle missing column infos.
  if ($column_infos == null) {
    $column_infos = build_default_column_infos($data);
  }

  // sort instructions
  $sort_field = getattr($options, 'sort_field',
                        getattr($options, 'default_sort_field'));
  $sort_direction = getattr($options, 'sort_direction',
                            getattr($options, 'default_sort_direction', SORT_ASC));

  // Table header: Column names.
  $result .= "<thead><tr>";
  foreach ($column_infos as $column) {
    $args = array();
    if (isset($column['css_class'])) {
      $args['class'] = $column['css_class'];
      if (isset($column['title_css_class'])) {
        $args['class'] .= ' ' . $column['title_css_class'];
      }
    }
    if (isset($column['css_style'])) {
      $args['style'] = $column['css_style'];
    }

    $key = getattr($column, 'key');
    if (isset($column['html_title'])) {
      $caption = getattr($column, 'html_title', $key);
    } else {
      $caption = html_escape(getattr($column, 'title', $key));
    }

    // sortable columns
    if ($key && getattr($column, 'sortable')) {
      if ($key == $sort_field) {
        $a_direction = (SORT_ASC == $sort_direction ? SORT_DESC : SORT_ASC);
        $span_class = ' sort-field-active';
      }
      else {
        $a_direction = $sort_direction;
        $span_class = '';
      }

      $caption .= '&nbsp;<span class="sort-field'.html_escape($span_class).'">'
        ._format_sort_link($options, $key, $a_direction)
        .'</span> ';
    }

    $result .= format_tag('th', $caption, $args, false);
  }
  $result .= "</tr></thead>";

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

    // row style class
    $func = getattr($options, 'row_style', null);
    $class = '';
    if ($func && is_callable($func)) {
      $class .= $func($row);
    }

    if (!$class) {
      $result .= '<tr>';
    }
    else {
      $result .= '<tr class="'.html_escape($class).'">';
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
    $result .= "</tr>\n";
  }

  $result .= "</tbody>";

  $result .= "</table>";

  // Paging.
  if (getattr($options, 'pager_style', 'none') != 'none') {
    $result .= format_pager($options);
  }

  return $result;
}

// Internal for format_table
function _format_sort_link($options, $sort_field, $sort_direction) {
  $url_args = getattr($options, 'url_args', $_GET);
  $param_prefix = getattr($options, 'param_prefix', '');

  $url_args[$param_prefix.'sort_field'] = $sort_field;
  if (SORT_DESC == $sort_direction) {
    $url_args[$param_prefix.'sort_desc'] = 1;
    $caption = '&or;';
  }
  else {
    if (isset($url_args[$param_prefix.'sort_desc']))
      unset($url_args[$param_prefix.'sort_desc']);
    $caption = '&and;';
  }

  return format_link(url_from_args($url_args), $caption, false);
}

?>
