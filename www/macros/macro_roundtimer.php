<?php

require_once(IA_ROOT_DIR . "common/db/round.php");

// Displays a timer that counts down to the beginning / end of a round
// given as parameter, then it refreshes the page. You can adjust the
// timer size by specifying the maximum number of units the timer
// may display or by not showing the message after the timer.
//
// Arguments:
//      round_id (required)           Round identifier (without prefix)
//      units (optional)              Maximum number of time units to display
//                                        (1 - 4, default: 4)
//      show_message (optional)       Shows a message to differenciate between
//                                    the countdown to the round start or end
//                                        (true/false, default: true)
//
// Examples:
//      RoundTimer(round_id="algoritmiada2010-runda1-9-10")

function macro_roundtimer($args) {
    $round_id = getattr($args, 'round_id');
    $units = getattr($args, 'units', 4);
    $show_message = getattr($args, 'show_message', "true");

    // validate arguments
    if (!$round_id) {
        return macro_error("Expecting parameter `round_id`");
    }
    if (!is_whole_number($units) && !(1 <= $units && $units <= 4)) {
        return macro_error("Invalid `units` parameter");
    }
    if (!in_array($show_message, Array("true", "false"))) {
        return macro_error("Invalid `show_message` parameter");
    }

    // fetch round & parameters
    if (!is_round_id($round_id)) {
        return macro_error("Invalid round id");
    }
    $round = round_get($round_id);
    if ($round) {
        $params = round_get_parameters($round_id);
    }

    // validate round id
    if (!$round) {
        return macro_error("Invalid round identifier");
    }
    if (!identity_can('round-view', $round)) {
        return macro_permission_error();
    }

    // for archives don't display anything
    if (is_null($round['type'])) {
        return;
    } else if ($round['type'] == 'archive') {
        return;
    }

    // if there is no start time/duration do nothing
    if (is_null($round['start_time'])) {
        return;
    }
    if (!isset($params['duration'])) {
        return;
    }

    $timer_id = "round-timer-" . $round_id;


    // timer script
    $html = '<span id = "' . html_escape($timer_id) . '">';
    $html .= '<script type="text/javascript">';
    $html .= 'newRoundTimer(' .
        '"' . html_escape($timer_id) . '"' .
        ',"' . html_escape(format_date(time(), "%Y/%m/%d %H:%M:%S %z")) . '"' .
        ',"' . html_escape(format_date($round['start_time'],
                                        "%Y/%m/%d %H:%M:%S %z")) . '"' .
        ',' . html_escape($params['duration']) .
        ',' . html_escape($units) .
        ',' . html_escape($show_message) . ');' .
      '</script>';
    $html .= '</span>';

    return $html;
}
?>
