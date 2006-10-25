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
    $display_rows = getattr($args, 'count', "0");
    if (!preg_match('/^[0-9]{1,4}$/', $display_rows)) {
        return macro_error("Invalid count parameter.");
    }

    // Make a list of round ids
    $roundStr = getattr($args, 'rounds', '');
    $rounds = preg_split('/\s*\|\s*/', $roundStr);

    // construct query
    //  - first, we need a WHERE condition for round_id.
    //    goal:  '<round-id-1>', '<round-id-2>' ... '<round-id-n>'
    $whereRound = '';
    foreach ($rounds as $round_id) {
        if ($whereRound) {
            $whereRound .= ', ';
        }
        $whereRound .= "'" . db_escape($round_id) . "'";
    }

    //  - SQL frame
    $query = "
        SELECT
            user_id, ia_user.username AS `username`,
            ia_user.full_name AS full_name, SUM(`score`) AS totalScore
        FROM ia_score
        LEFT JOIN ia_user ON ia_user.id = ia_score.user_id
        WHERE round_id IN (%s)
        GROUP BY user_id
        ORDER BY totalScore DESC
    ";
    if ($display_rows != 0) {
        $query .= " LIMIT 0, $display_rows";
    }
    $query = sprintf($query, $whereRound);

    // query database
    $rankings = db_fetch_all($query);

    log_backtrace();
    $column_infos = array(
            array('title' => 'ID', 'key' => 'user_id'),
            array('title' => 'User', 'key' => 'username'),
            array('title' => 'Nume', 'key' => 'full_name', 'rowform' => '_format_full_name'),
            array('title' => 'Scor', 'key' => 'totalScore'),
    );
    $options = array(
            'display_rows' => $display_rows,
    );

    return format_table($rankings, $column_infos, $options);
}

// Function for printing user link.
function _format_full_name($row) {
    return "<a href='".url('user/' . $row['username'])."'>{$row['full_name']}</a></td>";
}

?>
