<?php

require_once("macros/macros.php");
require_once("MyTextile.php");

function check_context($context)
{
    assert(is_array($context));
    assert(is_string($context['page_name']));
    if (isset($context['task']) || isset($context['task_parameters'])) {
        assert(is_array($context['task']));
        assert(is_array($context['task_parameters']));
    }
}

// This processes a big chunk of wiki-formatted text and returns html.
// The paramaters is an array of usefull information. macros can use them.
function wiki_process_text($content, $context) {
    check_context($context);
    error_reporting(0);
    $options = array('disable_html' => true);
    $weaver = new MyTextile($context, $options);
    return $weaver->process($content);
    error_reporting(65535);
}

?>
