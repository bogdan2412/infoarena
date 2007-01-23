<?php

// FIXME: This should be marged with macro_rankings.php

require_once(IA_ROOT . "www/format/table.php");
require_once(IA_ROOT . "www/format/pager.php");
require_once(IA_ROOT . "www/format/format.php");
require_once(IA_ROOT . "common/db/score.php");

function format_rank($row, $start_index) {
    static $rank = 0;
    $rank++;
    return $start_index + $rank; 
}

// Displays *interactive* rankings table displaying user *ratings*.
//
// Arguments:
//     count    (optional) how many to display at once
//
// Examples:
//      TopRated()
//      TopRated(count="10")
function macro_toprated($args) {
    $args['param_prefix'] = 'toprated_';
    $options = pager_init_options($args);

    $res = rating_toprated($options['first_entry'], $options['display_entries']);
    $rankings = $res['scores'];

    $column_infos = array(
        array(
            'title' => 'Loc',
            'key' => 'full_name',
            'rowform' => create_function('$row',
                                         'return format_rank($row, '.$options['first_entry'].');'),
            'css_class' => 'number rank',
        ),
        array(
            'title' => 'Nume',
            'key' => 'full_name',
            'rowform' => create_function('$row',
                                         'return format_user_normal($row["username"], $row["full_name"], $row["rating_cache"]);'),
        ),
        array(
            'title' => 'Rating',
            'key' => 'rating_cache',
            'rowform' => create_function('$row', 'return rating_scale($row[\'rating_cache\']);'),
            'css_class' => 'number rating',
        ),
    );
    $options['total_entries'] = $res['total_rows'];

    if (0 >= count($rankings)) {
        return macro_message('Nici un utilizator cu rating.');
    }
    else {
        return format_table($rankings, $column_infos, $options);
    }
}

?>
