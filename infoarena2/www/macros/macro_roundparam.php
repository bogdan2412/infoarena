<?php

// Displays a round field, be it a hard-coded field such as round title or a grader parameter such as `type`.
// NOTE: The macro employs a simple caching mechanism (via static variables, cache expires at the end of the request)
//       to avoid multiple database queries.
//
// Arguments:
//      round_id (required)           Round identifier (without round/ prefix)
//      param (required)              Parameter name. See the source code for possible values.
//      default_value (optional)      Display this when no such parameter is found
//
// Examples:
//      RoundParam(round_id="archive" param="title")
//      RoundParam(round_id="archive" param="type")
function macro_roundparam($args) {
    static $last_round_id = null;
    static $round;
    static $params;
    static $textblock;

    $round_id = getattr($args, 'round_id');
    $param = getattr($args, 'param');

    // validate arguments
    if (!$round_id) {
        return make_error_div("Expecting parameter `round_id`");
    }
    if (!$param) {
        return make_error_div("Expecting parameter `param`");
    }

    // fetch round, parameters & textblock
    if ($last_round_id != $round_id) {
        $round = round_get($round_id);
        if ($round) {
            $params = round_get_parameters($round_id);
            $textblock = round_get_textblock($round_id);
        }

        // remember
        $last_round_id = $round_id;
    }

    // validate round id
    if (!$round) {
        return make_error_div("Invalid round identifier");
    }

    // serve desired value
    switch ($param) {
        case 'title':
            return $textblock['title'];

        case 'id':
            return $task['id'];

        default:
            if (!isset($params[$param])) {
                if ($isset($args['default_value'])) {
                    return $args['default_value'];
                }
                else {
                    return make_error_div("Round doesn't have parameter '$param'");
                }
            }
    }
}
?>
