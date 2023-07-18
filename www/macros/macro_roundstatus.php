<?php

require_once(Config::ROOT . "common/db/round.php");

// Displays round status. Pretty div, floating (in textile) strongly recommended.
//
// Arguments:
//      round_id (required)           Round identifier (without prefix)
//
// Examples:
//      RoundStatus(round_id="preoni74c")
function macro_roundstatus($args) {
    $round_id = getattr($args, 'round_id');

    // validate arguments
    if ($round_id == null) {
        return macro_error("Expecting parameter `round_id`");
    }
    if (!is_round_id($round_id)) {
        return macro_error("Invalid round id '$round_id'");
    }

    // Fetch round
    $round = round_get($round_id);
    if (!$round) {
        return macro_error("Round '$round_id' not found");
    }

    if ($round['state'] == 'waiting') {
        return '<span class="round status waiting">Această rundă nu a început încă.</span>';
    } elseif ($round['state'] == 'running') {
        return '<span class="round status running">Runda este activă!</span>';
    } elseif ($round['state'] == 'complete') {
        return '<span class="round status complete">Runda s-a terminat.</span>';
    } else {
        return macro_error("Invalid round state");
    }
}
?>
