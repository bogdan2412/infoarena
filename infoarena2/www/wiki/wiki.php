<?php

require_once("Textile.php");

// This processes a big chunk of wiki-formatted text and returns html.
// The paramaters is an array of usefull information. macros can use them.
function wiki_process_text($wiki_text, $parameters) {
    $weaver = build_weaver();
    return $weaver->process($wiki_text);
}

// Build an instance of the textile processor.
// Config here.
function build_weaver()
{
    return new Textile();
}

?>
