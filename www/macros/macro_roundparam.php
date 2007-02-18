<?php

require_once(IA_ROOT_DIR . "common/db/round.php");

// Displays a round field, be it a hard-coded field such as round title or a grader parameter such as `type`.
// NOTE: The macro employs a simple caching mechanism (via static variables, cache expires at the end of the request)
//       to avoid multiple database queries.
//
// Arguments:
//      round_id (required)           Round identifier (without prefix)
//      param (required)              Parameter name. See the source code for possible values.
//      default_value (optional)      Display this when no such parameter is found
//
// Examples:
//      RoundParam(round_id="archive" param="title")
//      RoundParam(round_id="archive" param="type")
function macro_roundparam($args) {
    $round_id = getattr($args, 'round_id');
    $param = getattr($args, 'param');

    // validate arguments
    if (!$round_id) {
        return macro_error("Expecting parameter `round_id`");
    }
    if (!$param) {
        return macro_error("Expecting parameter `param`");
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

    // serve desired value
    switch ($param) {
        case 'title':
            return htmlentities($round['title']);

        case 'start_time':
            return format_date($round['start_time']);

        case 'id':
            return htmlentities($round['id']);

        default:
            if (!isset($params[$param])) {
                if (isset($args['default_value'])) {
                    return htmlentities($args['default_value']);
                } else {
                    return macro_error("Round doesn't have parameter '$param'");
                }
            } else {
                return htmlentities($params[$param]);
            }
    }
}
?>