<?php

// Displays a star-rating box.
//
// Arguments:
//      rating (required)   decimal number of stars awarded
//      scale (optional)    maximum number of stars, default is 5
//      type (optional)     star theme to use. default is "normal"
//                          other options: "small"
//
// Examples:
//      Stars(rating="2.5")
//      Stars(rating="4.5" type="small")
function macro_stars($args) {
    $rating = getattr($args, 'rating');
    $scale = getattr($args, 'scale', 5);
    $type = getattr($args, 'type', 'normal');

    if (is_null($rating)) {
        return macro_error('Expecting argument `rating` '
                           .'(number of stars awarded)');
    }
    if (!is_numeric($rating) || !is_numeric($scale) || $rating > $scale) {
        return macro_error('Invalid `rating` / `scale` argument');
    }
    if ($type != 'small' && $type != 'normal') {
        return macro_error('No such star theme');
    }

    // round first decimal to .0 or .5
    $rating = round(2 * $rating) / 2;

    $html = "<span class=\"star-rating\">";
    for ($i = 0; $i < $scale; ++$i) {
        if ($i < floor($rating))
            $img = 'full';
        elseif ($i >= $rating)
            $img = 'empty';
        else
            $img = 'half';

        $url = url_absolute(url_static("images/stars/{$type}-{$img}.png"));
        $html .= "<img src=\"".html_escape($url)."\" alt=\"{$type}\">";
    }

    // add hidden text to allow js sorting
    $html .= "<span class='hidden'>" . $rating . "/" . $scale . "</span>";

    $html .= "</span>";

    return $html;
}

?>
