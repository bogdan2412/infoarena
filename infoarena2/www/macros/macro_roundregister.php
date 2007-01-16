<?php

require_once(IA_ROOT."common/db/round.php");
require_once(IA_ROOT."www/macros/macro_include.php");

// FIXME: round registration disabled.
// Display registration invitation for a round when user is not registered.
// If user is already registered, display a confirmation message instead.
// 
// Arguments:
//      round_id (required)
//          - valid round id
//
// Examples:
//      RoundRegister(page_invitation="template/roundinvitation")
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

    // check permission
    global $identity_user;
    $is_registered = $identity_user && round_is_registered($round['id'], $identity_user['id']);

    if ($is_registered) {
        return "Esti inregistrat in runda '".$round['title']."'. ".
               "<a href= ".htmlentities(url_round_register_view($round['id'])).">".
               "Vezi cine mai este inregistrat!</a>";
    }
    else {
        return "Nu esti inregistrat in runda '".$round['title']."'. ".
               "<a href= ".htmlentities(url_round_register($round['id'])).">".
               "Inregistreaza-te!</a>";
    }

}

?>
