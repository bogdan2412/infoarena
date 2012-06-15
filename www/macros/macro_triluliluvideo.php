<?php

// Displays a trilulilu video
//
// Arguments:
//      user (required)             Trilulilu Video author username
//      id (required)               Trilulilu Video document ID
//      width (optional)            default 448px
//      height (optional)           default 386px
//
// Examples:
//      ==TriluliluVideo(user="microsoft" id="4bbc23d5de1524")==

function macro_triluliluvideo($args) {
    $doc_user = html_escape(getattr($args, 'user'));
    $doc_id = html_escape(getattr($args, 'id'));
    $width = getattr($args, 'width', 448);
    $height = getattr($args, 'height', 386);

    if (!$doc_user) {
        return macro_error('Expecting argument `user` (Trilulilu Video Author Username)');
    }

    if (!$doc_id) {
        return macro_error('Expecting argument `id` (Trilulilu Video Id)');
    }

    if (!is_whole_number($width) || !is_whole_number($height) || $width < 50
        || $width > 700 || $height < 50 || $height > 700) {
        return macro_error('Invalid `width` / `height` argument');
    }

    $url = "//www.trilulilu.ro/embed-video/{$doc_user}/{$doc_id}";

    $html = "<script type=\"text/javascript\" language=\"javascript\" ".
            "src=\"{$url}\">".
            "</script>".
            "<script type=\"text/javascript\" language=\"javascript\">".
            "show_{$doc_id}({$width}, {$height});".
            "</script>";

    return $html;
}
