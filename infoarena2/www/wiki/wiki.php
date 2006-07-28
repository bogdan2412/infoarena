<?php

@require_once("Textile.php");
@require_once("MyTextile.php");

error_reporting(0);

// This processes a big chunk of wiki-formatted text and returns html.
// The paramaters is an array of usefull information. macros can use them.
function wiki_process_text($page_content, $page_name) {
    $weaver = new MyTextile($page_name);
    return $weaver->process($page_content);
}

?>
