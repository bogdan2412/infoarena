<?php

require_once("format_table.php");

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
    // How many rows to display at a time.
    $display_rows = getattr($args, 'count', IA_DEFAULT_ROWS_PER_PAGE);
    if (!preg_match('/^[0-9]{1,4}$/', $display_rows)) {
        return macro_error("Invalid count parameter.");
    }

    // First row.
    $first_row = request('start', 0);
    if ($first_row < 0) {
        flash_error("Numar de pagina invalid.");
        $first_row = 0;
    }

    // Rounds parameters
    $roundstr = getattr($args, 'rounds', '');
    if ($roundstr == '') {
        return macro_error("Parameters 'rounds' is required.");
    }
    $rounds = preg_split('/\s*\|\s*/', $roundstr);

    // FIXME: user/ task parameters.

    $res = score_get("score", null, null, $rounds, $first_row, $display_rows);
    $rankings = $res['scores'];

    $column_infos = array(
            array(
                'title' => 'Nume',
                'key' => 'user_full',
                'rowform' => create_function('$row',
                        'return format_user_normal($row["user_name"], $row["user_full"]);'),
            ),
            array('title' => 'Scor', 'key' => 'score'),
    );
    $options = array(
            'display_rows' => $display_rows,
            'pager_style' => 'standard',
            'total_rows' => $res['total_rows'],
            'first_row' => $first_row,
    );

    return format_table($rankings, $column_infos, $options);
}

?>
