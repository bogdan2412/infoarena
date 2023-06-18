<?php

// Displays DIV box that loads its content client-side via XmlHttpRequest
// You may only display one single remotebox on a page at a time.
//
// Arguments
//      url (required)      *Absolute* URL to load @ client-side
//
// NOTE: This macro requires special user permissions since it poses quite
// a few security concerns.
function macro_remotebox($url, $bypass_security = false) {
    if (!$bypass_security && !identity_can('macro-remotebox')) {
        return macro_permission_error();
    }

    if (!$url) {
        return macro_error('Expecting argument `url`');
    }

    $buffer = '';
    $buffer .= '<div id="remotebox">remote content</div>';
    $buffer .= '<script>RemoteBox_Url="';
    $buffer .= html_escape($url);
    $buffer .= '";</script>';

    return $buffer;
}

?>
