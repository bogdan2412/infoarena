<?php

//  This macro is used to display a chart with the distribution of the rating
//  and the position of a user.
//
//  Arguments:
//      user (required)
//
//  Example:
//      ==DistributionChart(user="gigel")==
function macro_distributionchart($args) {
    $user = getattr($args, 'user');

    if (is_null($user)) {
        return macro_error('Expected argument `user`');
    }

    $html = '<br /><div id="distribution-chart"></div><br />'
    . '<script type="text/javascript"> swfobject.embedSWF("' . html_escape(url_static("swf/open-flash-chart.swf"))
    . '", "distribution-chart", "560", "280", "9.0.0", null, {"data-file":"' . html_escape(url_home() . 'plot/distribution?user=' . $user) . '"}); </script>';

    return $html;
}

?>

