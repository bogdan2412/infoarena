<?php

require_once(IA_ROOT."www/format/format.php");

// Formats HTML <UL> (unordered list)
// $items contains <LI> items. Any item must be one of:
//  - string containing raw HTML
//  - array(<raw HTML>, <array of LI attributes>)
function format_ul($items, $class = null) {
    $buffer = '';

    if (is_null($class)) {
        $buffer .= "<ul>\n";
    }
    else {
        $buffer .= "<ul class=\"{$class}\">\n";
    }

    foreach ($items as $item) {
        if (is_array($item)) {
            $text = $item[0];
            $attrib = $item[1];
        }
        else {
            $text = $item;
            $attrib = array();
        }

        $buffer .= "\t".format_tag("li", $attrib, $text);
    }

    $buffer .= "</ul>\n";

    return $buffer;
}

?>
