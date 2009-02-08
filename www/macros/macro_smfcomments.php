<?php

require_once(IA_ROOT_DIR.'www/macros/macro_remotebox.php');

// Displays all posts in given SMF topic as a comment thread. This macro is a
// wrapper for the more generic macro RemoteBox()
//
// Arguments:
//      topic_id (required) SMF numeric topic id
//
// Examples:
//      SmfComments( topic_id="400" )
function macro_smfcomments($args) {
    $topic_id = getattr($args, 'topic_id');

    if (is_null($topic_id)) {
        return macro_error('Expecting argument `topic_id`');
    }

    $args = array(
        'url' => IA_SMF_URL.'/ia_comments.php?topic_id='.$topic_id,
        'display' => 'show'
    );
    return macro_remotebox($args, true);
}

?>
