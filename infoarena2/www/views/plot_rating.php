<?php

require_once(IA_ROOT."common/rating.php");

// where to start plot from?
$range_start = "2004-01-01";
// today
$range_end = date("Y-m-d");

// gnuplot script
$script = "
set xdata time
set timefmt \"%Y-%m-%d\"
set format x \"%m/%y\"
set key right nobox
set grid

set title \"Evolutie rating pentru {$user['username']}\"

set style line 1 lt 1 lw 2 pt 3 ps 0.5
set style line 2 lt 3 lw 1 pt 5 ps 1.4
set xrange [\"{$range_start}\":\"{$range_end}\"]

set ylabel \"Rating\"
set yrange [150:1000]
";

if (1 <= count($history)) {
    $script .= "
plot \\
    \"%data%\" using 1:2 title \"Rating\" with lines ls 1, \\
    \"%data%\" using 1:2:3 title \"Deviatie\" with errorbars ls 2
";
}
else {
    // when nothing to plot, use a bogus (non-visible) function plot so that
    // gnuplot doesn't fail
    $script .= "
plot 0 title \"\" with lines ls 1
";
}

// plot data
// #date #rating #deviation
$data = '';
foreach ($history as $round_id => $round) {
    $timestamp = (int)$round['timestamp'];
    $data .= date("Y-m-d", $timestamp)
             ." ".rating_scale($round['rating'])." "
             .$round['deviation']."\n";
}

// render PNG
include(IA_ROOT.'www/views/gnuplot.php');

?>
