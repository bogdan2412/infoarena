<?php

require_once("format_table.php");

// Displays *interactive* rankings table summing up score points from a
// pre-defined set of contest rounds.
//
// Synopsis:
// == Rankings(rounds="preONI2007/1/9-10 | preONI2007/2/9-10") ==
function macro_rankings($args) {
    // get rounds ids
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
    $query = sprintf($query, $whereRound);

    // query database
    $rankings = db_fetch_all($query);

    function format_full_name($row) {
        return "<a href='".url('user/' . $row['username'])."'>{$row['full_name']}</a></td>";
    }

    $column_infos = array(
            array('title' => 'ID', 'key' => 'user_id'),
            array('title' => 'User', 'key' => 'username'),
            array('title' => 'Nume', 'key' => 'full_name', 'rowform' => 'format_full_name'),
            array('title' => 'Scor', 'key' => 'totalScore'),
    );

    return format_table($rankings, $column_infos);
}

?>
