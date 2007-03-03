<?php

// Displays a google video widget (EMBED object).
//
// Arguments:
//      id (required)       Google Video document ID
//      width (optional)    default 400px
//      height (optional)   default 326px
//
// Examples:
//      GoogleVideo( id="2535789311422139569" )
function macro_googlevideo($args) {
    $doc_id = getattr($args, 'id');
    $width = getattr($args, 'width', 400);
    $height = getattr($args, 'height', 326);

    if (is_null($doc_id)) {
        return macro_error('Expecting argument `id` (Google Video docId)');
    }
    if (!is_numeric($width) || !is_numeric($height) || $width < 50
        || $width > 700 || $height < 50 || $height > 700) {
        return macro_error('Invalid `width` / `height` argument');
    }

    $url = "http://video.google.com/googleplayer.swf?docId={$doc_id}&hl=en";

    $html = "<embed style=\"width: {$width}px; height: {$height}px;\" "
            ."id=\"VideoPlayback\" type=\"application/x-shockwave-flash\" "
            ."src=\"{$url}\" flashvars=\"\"></embed>";

    return $html;
}

?>
