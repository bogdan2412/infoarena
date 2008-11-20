<?php

// This is a macro to embed a published google spreadsheet
// 
// Arguments:
//      id (required)       Google Spreadsheet Key
//      width(optional)     default 1000px
//      heigth(optional)    default 600px 
//
// Example: ==GoogleSpreadsheet(id="pqhshqsOa5Pa3Z39x86jSXg" height = "200" width = "500")==

function macro_googlespreadsheet($args) {
    $doc_key = html_escape(getattr($args, 'id'));
    $width = getattr($args, 'width', 1000);
    $height = getattr($args, 'height', 600);

    if (!$doc_key) {
        return macro_error('Expected document `id` (Google Spreadsheet key)');
    }

    if (!is_whole_number($width) || !(is_whole_number($height)) ||
        $height < 50 || $width < 50 || $height > 1600 || $width > 2000) {
        return macro_error('Invalid `width` / `height` argument');
    }

    $url = "http://spreadsheets.google.com/pub?key=$doc_key&amp;widget=true";
    $html = "<iframe width=\"$width\" height=\"$height\" ".
            "frameborder=\"0\" src=\"$url\"></iframe>";

    return $html;
}

?>
