<?php

require_once(Config::ROOT."www/format/format.php");

// Wrapper for format_ulol
function format_ul($items, $class = null) {
    return format_ulol('ul', $items, $class);
}

// Wrapper for format_ulol
function format_ol($items, $class = null) {
    return format_ulol('ol', $items, $class);
}

// Formats HTML <UL>/<OL> (unordered/ordered list)
// $items contains <LI> items. Any item must be one of:
//  - string containing raw HTML
//  - array(<raw HTML>, <dictionary of LI attributes>)
function format_ulol($tag, $items, $class) {
    log_assert($tag == 'ul' || $tag == 'ol', "Invalid tag");
    $buffer = '';

    if (is_null($class)) {
        $buffer .= "<{$tag}>\n";
    } else {
        $buffer .= "<{$tag} class=\"{$class}\">\n";
    }

    foreach ($items as $item) {
        if (is_array($item)) {
            $text = $item[0];
            $attrib = $item[1];
        } else {
            $text = $item;
            $attrib = array();
        }

        $buffer .= "\t".format_tag("li", $text, $attrib, false);
    }

    $buffer .= "</{$tag}>\n";

    return $buffer;
}

?>
