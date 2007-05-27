<?php

require_once(IA_ROOT_DIR.'common/dataset.php');
require_once(IA_ROOT_DIR.'www/format/table.php');
require_once(IA_ROOT_DIR.'www/format/format.php');
require_once(IA_ROOT_DIR.'www/format/list.php');
require_once(IA_ROOT_DIR."www/format/form.php");
require_once(IA_ROOT_DIR."www/format/filters.php");

function format_datagrid($dataset, $columns, $user_options = array()) {
    // merge user provided options with defaults
    $options = array(
        'first_entry' => 0,
        'display_entries' => 20,
        'show_count' => true,
        'pager_style' => 'standard',
    );
    $options = $user_options + $options;

    // range & pagination
    $options['total_entries'] = $dataset->count();
    if ($options['first_entry'] > $options['total_entries']) {
        // FIXME: Overflow checking should somehow be done in format_table
        $options['first_entry'] = 0;
    }
    $dataset->limit($options['first_entry'], $options['display_entries']);

    // sorting
    if ($dataset instanceof Sortable) {
        $sort_field = getattr($options, 'sort_field');
        if ($sort_field) {
            $sort_direction = getattr($options, 'sort_direction', SORT_ASC);
        }
        else {
            $sort_field = getattr($options, 'default_sort_field');
            $sort_direction = getattr($options, 'default_sort_direction',
                                      SORT_ASC);
        }

        if (!is_null($sort_field) && $dataset->isSortableField($sort_field)) {
            $dataset->sortBy($sort_field, $sort_direction);
        }
        for ($i = 0; $i < count($columns); ++$i) {
            $col =& $columns[$i];
            if (isset($col['key']) && $dataset->isSortableField($col['key'])) {
                $col['sortable'] = true;
            }
        }
    }

    // write HTML
    $html_buffer = '<div class="datagrid">';

    // filtering
    if ($dataset instanceof Filterable) {
        $html_buffer .= format_dataset_filters($dataset, $options);
    }

    // base table
    $html_buffer .= format_table(unroll($dataset), $columns, $options);

    $html_buffer .= '</div>';


    return $html_buffer;
}

// Parse request parameters for datagrid into $view_options
function datagrid_parse_request($dataset, &$view_options) {
    if (!isset($view_options['first_entry'])) {
        $view_options['first_entry'] = request('first_entry', 0);
    }

    // sorting
    if ($dataset instanceof Sortable) {
        if (!isset($view_options['sort_field'])) {
            $view_options['sort_field'] = request('sort_field');
        }
        if (!isset($view_options['sort_direction'])) {
            $view_options['sort_direction'] = (request('sort_desc') ? SORT_DESC : SORT_ASC);
        }
    }

    // filtering
    if ($dataset instanceof Filterable) {
        filters_parse_request($dataset, $view_options); 
    }
}

function column($key, $title = null) {
    $column = array('key' => $key);
    if (!is_null($title))
        $column['title'] = $title;
    return $column;
}

function column_user($type = 'tiny') {
    log_assert(array_search($type, array('tiny', 'normal', 'link')) !== false);
    $body = 'return format_user_'.$type.'($row["username"], $row["full_name"], $row["rating_cache"]);';
    return
        array(
            'title' => 'Utilizator',
            'key' => 'username',
            'rowform' => create_function_cached('$row', $body),
        );
}

function column_number($key, $title = null, $decimals = 0) {
    $column = array(
        'key' => $key,
        'css_class' => 'number',
    );
    if (!is_null($title)) {
        $column['title'] = $title;
    }
    if ($decimals > 0) {
        $body = 'return number_format($val, '.$decimals.', ",", " ");';
        $column['valform'] = create_function_cached('$val', $body);
    }

    return $column;
}

function column_rating($key = 'rating_cache', $title = 'Rating') {
    $column = array(
        'key' => $key,
        'title' => $title,
        'css_class' => 'number',
        'valform' => 'rating_scale',
    );

    return $column;
}

function column_date($key, $title = null, $date_format = 'Y-m-d H:i:s') {
    $column = array(
        'key' => $key,
        'css_class' => 'date',
    );
    if (!is_null($title)) {
        $column['title'] = $title;
    }
    $body = 'return date(\''.addslashes($date_format).'\', db_date_parse($val));';
    $column['valform'] = create_function_cached('$val', $body);

    return $column;
}


?>
