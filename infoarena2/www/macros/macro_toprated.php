<?php

// FIXME: This should be marged with macro_rankings.php

require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "common/db/score.php");

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

    $rankings = get_users_by_rating_range($options['first_entry'], $options['display_entries']);

    $column_infos = array(
        array(
            'title' => 'Loc',
            'key' => 'position',
            'css_class' => 'number rank',
        ),
        array(
            'title' => 'Nume',
            'key' => 'full_name',
            'rowform' => create_function_cached('$row',
                                         'return format_user_normal($row["username"], $row["full_name"], $row["rating_cache"]);'),
        ),
        array(
            'title' => 'Rating',
            'key' => 'rating_cache',
            'rowform' => create_function_cached('$row', 'return rating_scale($row[\'rating_cache\']);'),
            'css_class' => 'number rating',
        ),
    );
    if (pager_needs_total_entries($options)) {
        $options['total_entries'] = get_users_by_rating_count();
    }

    if (0 >= count($rankings)) {
        return macro_message('Nici un utilizator cu rating.');
    } else {
        return format_table($rankings, $column_infos, $options);
    }
}

?>
