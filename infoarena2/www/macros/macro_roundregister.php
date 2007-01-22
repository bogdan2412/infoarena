<?php

require_once(IA_ROOT."common/db/round.php");
require_once(IA_ROOT."www/macros/macro_include.php");

// Display registration invitation for a round when user is not registered.
// If user is already registered, display a confirmation message instead.
// 
// Arguments:
//      round_id (required)
//          - valid round id
//
// Examples:
//      RoundRegister()
function macro_roundregister($args) {
    $round_id = getattr($args, 'round_id');
    if (!is_round_id($round_id)) {
        return macro_error('Invalid round identifier');
    }

    // validate round id
    $round = round_get($round_id);
    if (!$round) {
        return macro_error('Invalid round identifier');
    }

    global $identity_user;
    $is_registered = $identity_user && round_is_registered($round['id'], $identity_user['id']);

    if ($is_registered) {
        $class = "round-registered";
        $msg = "Te-ai inscris la <em>{$round['title']}</em>."
               ." <a href=\"".htmlentities(url_round_register_view($round['id']))."\">"
               ."Vezi cine s-a mai inscris"
               ."</a>.";
    }
    else {
        // too late?
        if ('waiting' != $round['state']) {
            $class = "round-expired";
            $msg = "Nu se mai pot face inscrieri la <em>{$round['title']}</em>."
                   ." Inscrierile s-au inchis la ora "
                   .format_date($round['start_time'], "%H:%M, %d&nbsp;%b&nbsp;%Y.")
                   ."<p><a href=\"".htmlentities(url_round_register_view($round['id']))."\">"
                   ."Vezi cine s-a inscris"
                   ."</a></p>";
        }
        else {
            $class = "round-register";
            $msg = "Nu esti inscris la <em>{$round['title']}</em>! Ca sa participi"
                   ." la aceasta runda trebuie sa te inscrii pana la ora "
                   .format_date($round['start_time'], "%H:%M, %d&nbsp;%b&nbsp;%Y.")
                   ."<p><a href=\"".htmlentities(url_round_register($round['id']))."\">"
                   ."<strong>Inscrie-te acum!</strong></a> &nbsp; "
                   ." <a href=\"".htmlentities(url_round_register_view($round['id']))."\">"
                   ."Vezi cine e inscris"
                   ."</a></p>";
        }
    }

    $msg = "<div class=\"{$class}\">{$msg}</div>";

    return $msg;
}

?>
