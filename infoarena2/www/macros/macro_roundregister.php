<?php

require_once(IA_ROOT."common/db/round.php");

// FIXME: round registration disabled.
// Display registration invitation for a round when user is not registered.
// If user is already registered, display a confirmation message instead.
// 
// Arguments:
//      round_id (required)
//          - valid round id
//      page_invitation (optional)
//          - Textblock to include when user needs to register.
//          - default value is template/roundinvitation
//      page_registered (optional)
//          - Textblock to include when user is already registered 
//          - default value is null (nothing to include)
//
// Examples:
//      RoundRegister(page_invitation="template/roundinvitation")
/*function macro_roundregister($args) {
    $page_invitation = getattr($args, 'page_invitation', 'template/roundinvitation');
    $page_registered = getattr($args, 'page_registered');
    $round_id = getattr($args, 'round_id');

    // validate round id
    $round = round_get($round_id);
    if (!$round) {
        return macro_error('Invalid round identifier');
    }

    // check permission
    global $identity_user;
    $is_registered = $identity_user && round_is_registered($round['id'], $identity_user['id']);

    // include proper template
    $include_page = (!$is_registered ? $page_invitation : $page_registered);
    if (!is_null($include_page)) {
        require_once('macros/macro_include.php');
        $args = array(
            'page' => $include_page,
            'round_id' => $round['id']
        );
        return macro_include($args);
    }
    else {
        return '';
    }
}*/

?>
