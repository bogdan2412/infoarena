<?php

require_once(IA_ROOT_DIR.'www/macros/macro_remotebox.php');

// Displays all posts in given SMF topic as a comment thread. This macro is a
// wrapper for the more generic macro RemoteBox()
//
// Arguments:
//      topic_id (required) - SMF numeric topic id
//      display (required)  - parameter for visibility
//      max_comm (optional) - number of comments showed
//
// Examples:
//      SmfComments( topic_id="400" display="show" )
//      SmfComments( topic_id="400" display="hide" max_comm="5")
function macro_smfcomments($args) {
    $topic_id = getattr($args, 'topic_id');
    $display = getattr($args, 'display');
    $max_comm = getattr($args, 'max_comm');

    if (is_null($topic_id)) {
        return macro_error('Expecting argument `topic_id`');
    }

    if (is_null($display)) {
        return macro_error('Expecting argument `display`');
    }

    if ($display != 'show' && $display != 'hide') {
        return macro_error('Wrong value for argument `display`');
    }

    $args = array(
        'url' => IA_SMF_URL.'/ia_comments.php?topic_id='.$topic_id,
        'display' => $display
    );
    if(!is_null($max_comm)) {
        if(is_whole_number($max_comm)) {
            $args['max_comm'] = $max_comm;
        } else {
            return macro_error('Wrong value for argument `max_comm`');
        }
    }

    return macro_remotebox($args, true);
}

?>
