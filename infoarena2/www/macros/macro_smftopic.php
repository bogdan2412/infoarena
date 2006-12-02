<?php

require_once(IA_ROOT.'www/macros/macro_remotebox.php');

// Displays last posts in given SMF topic. This macro is a wrapper
// for the more generic macro RemoteBox()
//
// Arguments:
//      topic_id (required) SMF numeric topic id
//      count    (optional) number of recent messages to display
//               default is 5
// Examples:
//      SmfTopic( topic_id="400" )
function macro_smftopic($args) {
    $topic_id = getattr($args, 'topic_id');
    $count = getattr($args, 'count', 5);

    if (is_null($topic_id)) {
        return macro_error('Expecting argument `topic_id`');
    }
    if (!is_numeric($count) || 0 >= $count) {
        return macro_error('Invalid `count` argument');
    }

    $args = array(
        'url' => IA_URL.'sdhack/?file=ia_recentposts&topic_id='.$topic_id.'&count='.$count
    );
    return macro_remotebox($args, true);
}

?>
