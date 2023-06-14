<?php

require_once(IA_ROOT_DIR."common/db/round.php");
require_once(IA_ROOT_DIR."www/macros/macro_include.php");

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
    $round_parameters = round_get_parameters($round_id);
    if (!getattr($round_parameters, "rating_update")) {
        return "";
    }

    global $identity_user;
    $is_registered = $identity_user && round_is_registered($round['id'], $identity_user['id']);

    if ($is_registered) {
        $class = "round-registered";
        $msg = "<p>Te-ai inscris la <em>".html_escape($round['title'])."</em>."
               ." <a href=\"".html_escape(url_round_register_view($round['id']))."\">"
               ."Vezi cine s-a mai inscris"
               ."</a>.</p>";

        if ($round['state'] == 'waiting') {
            $msg .= "<p>In caz ca nu mai poti participa te poti deinscrie"
                    ." <a href=\"".html_escape(url_round_register($round['id']))
                    ."\">aici</a>.</p>";
        }
    }
    else {
        // too late?
        if ('waiting' == $round['state']) {
            $class = "round-register";
            $msg = "<p>Nu esti inscris la "
                   ."<em>".html_escape($round['title'])."</em>!   "
                   ."Daca vrei sa ti se modifice modifice rating-ul dupa "
                   ."acest concurs trebuie sa te inscrii pana la ora "
                   .format_date($round['start_time'], 'HH:mm, d MMMM yyyy').".</p>"
                   ."<p><a href=\"".html_escape(url_round_register($round['id']))."\">"
                   ."<strong>Inscrie-te acum!</strong></a> &nbsp; "
                   ." <a href=\"".html_escape(url_round_register_view($round['id']))."\">"
                   ."Vezi cine e inscris"
                   ."</a></p>"
                   ."<p>Poti sa participi la concurs si fara sa te inscrii "
                   ."insa nu ti se va schimba rating-ul.</p>";
        }
        elseif ('running' == $round['state']) {
            $class = "round-expired";
            $msg = "<p>Nu se mai pot face inscrieri la "
                   ."<em>".html_escape($round['title'])."</em> "
                   ."<strong><em>insa mai poti participa</em></strong>.</p>"
                   ."<p>Trebuia sa te inscrii inainte de ora "
                   .format_date($round['start_time'], 'HH:mm, d MMMM yyyy')
                   ." daca vroiai sa ti se modifice rating-ul la finalul "
                   ."rundei. Acum poti sa participi dar nu ti se va modifica "
                   ."rating-ul.</p>"
                   ."<p><a href=\"".html_escape(url_round_register_view($round['id']))."\">"
                   ."Vezi cine s-a inscris"
                   ."</a></p>";
        }
        else {
            // 'complete' == $round['state']
            $class = "round-expired";
            $msg = "<p>Nu se mai pot face inscrieri la "
                   ."<em>".html_escape($round['title'])."</em>. "
                   ."Runda s-a incheiat.</p>"
                   ."<p><a href=\"".html_escape(url_round_register_view($round['id']))."\">"
                   ."Vezi cine s-a inscris"
                   ."</a></p>";
        }
    }

    $msg = "<div class=\"{$class}\">{$msg}</div>";

    return $msg;
}

?>
