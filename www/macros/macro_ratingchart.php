<?php

//  This macro is used to display a chart with the rating evolution of a user.
//
//  Arguments:
//      user (required)
//
//  Example:
//      ==RatingChart(user="gigel")==
function macro_ratingchart($args) {
    $user = getattr($args, 'user');

    if (is_null($user)) {
        return macro_error('Expected argument `user`');
    }

    $html = '<br /><div id="rating-chart"></div><br />'
    . '<script type="text/javascript"> swfobject.embedSWF("' . html_escape(url_static("swf/open-flash-chart.swf"))
    . '", "rating-chart", "560", "280", "9.0.0", null, {"data-file":"' . html_escape(url_home() . 'plot/rating?user=' . $user) . '"}); </script>';

    return $html;
}

?>

