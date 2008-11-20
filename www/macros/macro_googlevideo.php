<?php
require_once(IA_ROOT_DIR . "common/common.php");

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
    $doc_id = html_escape(getattr($args, 'id'));
    $width = getattr($args, 'width', 400);
    $height = getattr($args, 'height', 326);

    if (!$doc_id) {
        return macro_error('Expecting argument `id` (Google Video docId)');
    }

    if (!is_whole_number($width) || !is_whole_number($height) || $width < 50
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
