<?php

@require_once("Textile.php");
@require_once("MyTextile.php");

// This processes a big chunk of wiki-formatted text and returns html.
// The paramaters is an array of usefull information. macros can use them.
function wiki_process_text($wiki_text, $parameters) {
    // TODO: save error_reporting level before resetting it and restore it
    // before return
    $weaver = new MyTextile(getattr($parameters, 'page_name'));
    return $weaver->process($wiki_text);
}

error_reporting(1 << 16 - 1);
?>
