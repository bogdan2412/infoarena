<?php
require_once(IA_ROOT_DIR . "common/common.php");

// Displays a youtube video widget (EMBED object).
//
// Arguments:
//      id (required)               Youtube Video document ID
//      width (optional)            default 425px
//      height (optional)           default 344px
//
// Examples:
//      YoutubeVideo(id="UXT_voYw9FY")
function macro_youtubevideo($args) {
    $doc_id = html_escape(getattr($args, 'id'));
    $width = getattr($args, 'width', 425);
    $height = getattr($args, 'height', 344);

    if (is_null($doc_id)) {
        return macro_error('Expecting argument `id` (Youtube Video docId)');
    }

    if (!is_whole_number($width) || !is_whole_number($height) || $width < 50
        || $width > 700 || $height < 50 || $height > 700) {
        return macro_error('Invalid `width` / `height` argument');
    }

    $url = "http://www.youtube.com/v/{$doc_id}";

    $html = "<embed src=\"{$url}\" type=\"application/x-shockwave-flash\" "
            ."allowscriptaccess=\"always\" allowfullscreen=\"true\" "
            ."width=\"{$width}\" height=\"{$height}\"></embed>";

    return $html;
}

?>
