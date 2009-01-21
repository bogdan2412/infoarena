<?php

require_once(IA_ROOT_DIR . "www/format/table.php");
require_once(IA_ROOT_DIR . "www/format/pager.php");
require_once(IA_ROOT_DIR . "www/format/format.php");
require_once(IA_ROOT_DIR . "common/db/score.php");

// Displays *interactive* rankings table summing up score points from a
// pre-defined set of contest rounds.
//
// Arguments:
//     rounds   (required) a | (pipe) separated list of round names.
//     count    (optional) how many to display at once, defaults to infinity
//
// Examples:
//      Rankings(rounds="preONI2007/1/a | preONI2007/2/a")
//      Rankings(rounds="preONI2007/1/a | preONI2007/2/a" count="10")
function macro_rankings($args) {
    $args['param_prefix'] = 'rankings_';
    if (isset($args['count'])) {
        $args['display_entries'] = $args['count'];
    }
    $options = pager_init_options($args);
    $options['show_count'] = true;

    // Rounds parameters
    $roundstr = getattr($args, 'rounds', '');
    if ($roundstr == '') {
        return macro_error("Parameters 'rounds' is required.");
    }
    $rounds = preg_split('/\s*\|\s*/', $roundstr);

    // FIXME: user / task parameters.
    $rankings = score_get_range("score", null, null, $rounds, "user_id", $options['first_entry'], $options['display_entries'], true);

    $column_infos = array(
        array(
            'title' => 'Pozitie',
            'key' => 'pos',
            'rowform' => create_function_cached('$row', 'return $row["ranking"];'),
            'css_class' => 'number rank'
        ),
        array(
            'title' => 'Nume',
            'key' => 'user_full',
            'rowform' => create_function_cached('$row',
                                         'return format_user_normal($row["user_name"], $row["user_full"], $row["user_rating"]);'),
        ),
        array(
            'title' => 'Scor',
            'key' => 'score',
            'rowform' => create_function_cached('$row', 'return round($row[\'score\']);'),
            'css_class' => 'number score'
        ),
    );

    if (pager_needs_total_entries($options)) {
        $options['total_entries'] = score_get_count("score", null, null, $rounds, 'user_id');
    }

    if (0 >= count($rankings)) {
        return macro_message('Nici un rezultat inregistrat pentru aceasta runda.');
    } else {
        return format_table($rankings, $column_infos, $options);
    }
}

?>
