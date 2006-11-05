<?php

// Displays DIV box that loads its content client-side via XmlHttpRequest
// You may only display one single remotebox on a page at a time.
//
// Arguments
//      url (required)      *Absolute* URL to load @ client-side
//
// NOTE: This macro requires special user permissions since it poses quite
// a few security concerns.
function macro_remotebox($args) {
    $url = getattr($args, 'url');
    if (!$url) {
        return macro_error('Expecting argument `url`'); 
    }

    $buffer = '';
    $buffer .= '<div id="remotebox"></div>';
    $buffer .= '<script type="text/javascript">RemoteBox_Url="'.$args['url'].'";</script>';

    return $buffer;
}

?>
