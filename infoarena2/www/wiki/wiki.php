<?php

require_once("Textile.php");

// Process a given page, by name. Return html block.
function wiki_process_page($wiki_page, $parameters) {
    // FIXME: write me
    // FIXME: mysql here.
    // get the latest version of the page.
    // wiki_process_text the latest page.text
    return wiki_process_text("_page_ *{$wiki_page}*", $parameters);
}

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
