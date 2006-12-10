<?php

require_once(IA_ROOT."common/rating.php");

// date range
if (2 <= count($history)) {
    $keys = array_keys($history);
    $range_start = $history[$keys[0]]['timestamp'] - 30*24*3600;
    $range_end = $history[$keys[count($history)-1]]['timestamp'] + 30*24*3600;
}
else {
    // 2004-01-01
    $range_start = mktime(1, 0, 0, 1, 1, 2004);
    $range_end = time();
}
// compute months between date range to show as xtics
list($dy, $dm, $dd) = split('-', date('Y-m-d', $range_start));
$i = 0;
$xtics = array();
while (true) {
    $dx = mktime(1, 0, 0, $dm + $i, $dd, $dy); 
    $xtics[] = date('Y-m-d', $dx);
    if ($dx > $range_end) {
        break;
    }
    $i += 2;
}

// format date ranges for gnuplot
$range_start = date('Y-m-d', $range_start);
$range_end = date('Y-m-d', $range_end);

// gnuplot script
$script = "
set xdata time
set timefmt \"%Y-%m-%d\"
set format x \"%b %y\"


set grid

set rmargin 2
set lmargin 5
set tmargin 12
set bmargin 2
set xtics nomirror
set ytics nomirror

set title \"Evolutie rating pentru {$user['username']} (".IA_URL.")\" 0,-.5

set style line 1 lt 1 lw 4 pt 3 ps 0.5
set style line 2 lt 3 lw 4 pt 7 ps 1.
set style line 3 lt 11 lw 3
set xrange [\"{$range_start}\":\"{$range_end}\"]

set xtics ('".join("', '", $xtics)."')
set xtic rotate by -20


set clip
";

// display round_id labels
$i = 1;
foreach ($history as $round_id => $round) {
    $date = date("Y-m-d", $round['timestamp']);
    $rating = rating_scale($round['rating']) + ($i % 2 ? 100 : -100);
    $align = ($i % 2 ? "left" : "right");
    $align = "left";
    $script .= "set label {$i} \"{$i}\" at \"{$date}\",{$rating} {$align} font \"Helvetica,17\" back tc lt 9\n";
    $i++;
}

// legend
$script .= "
set key right bottom box 3
set key width -1.5
";

if (1 <= count($history)) {
    // plot ratings, deviations & median
    $script .= "
plot \\
    \"%data%\" using 1:2 title \"Rating\" with lines ls 1, \\
    \"%data%\" using 1:2 smooth bezier title \"Medie\" with lines ls 3, \\
    \"%data%\" using 1:2:3 title \"Deviatie\" with errorbars ls 2
";
}
else {
    // when nothing to plot, use a bogus (non-visible) function plot so that
    // gnuplot doesn't fail
    $script .= "
set label \"(date insuficiente pentru a desena graficul)\" at graph 0.5,0.5 center
plot 0 notitle with lines ls 1
";
}

// plot data
// #date #rating #deviation
$data = '';
foreach ($history as $round_id => $round) {
    $timestamp = (int)$round['timestamp'];
    $data .= date("Y-m-d", $timestamp)
             ." ".rating_scale($round['rating'])." "
             .rating_scale($round['deviation'])."\n";
}

// render PNG
include(IA_ROOT.'www/views/gnuplot.php');

?>
