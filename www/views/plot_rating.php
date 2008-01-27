<?php

require_once(IA_ROOT_DIR."www/format/format.php");

// date range
if (2 <= count($history)) {
    $keys = array_keys($history);
    $range_start = $history[$keys[0]]['timestamp'];
    $range_end = $history[$keys[count($history)-1]]['timestamp'];
    $rating_start = $history[$keys[0]]['rating'];
    $rating_end = $rating_start;
    foreach ($history as $round_id => $round) {
        $rating_start = min($rating_start, $round['rating']);
        $rating_end = max($rating_end, $round['rating']);
    }
    $rating_start = rating_scale($rating_start)-50;
    $rating_end = rating_scale($rating_end)+50;
}
else {
    // 2004-01-01
    $range_start = mktime(1, 0, 0, 1, 1, 2004);
    $range_end = time();
    $rating_start = 0; $rating_end = 1000;
}
// compute months between date range to show as xtics
list($dy, $dm, $dd) = split('-', date('Y-m-d', $range_end));
$i = 0;
$xtics = array();
$scale = ceil(($range_end-$range_start)/(30*24*3600)/12);
while (true) {
    $dx = mktime(1, 0, 0, $dm - $i, $dd, $dy); 
    $xtics[] = date('Y-m-d', $dx);
    if ($dx < $range_start) {
        break;
    }
    $i += $scale;
}
$xtics = array_reverse($xtics);

// format date ranges for gnuplot
$range_start = date('Y-m-d', $range_start - 15*24*3600);
$range_end = date('Y-m-d', $range_end + 15*24*3600);

// gnuplot script
$script = "
set terminal postscript noenhanced
set locale \"ro_RO.UTF-8\"
set xdata time
set timefmt \"%Y-%m-%d\"
set format x \"%b %y\"
set grid

set rmargin 5
set lmargin 5
set tmargin 12
set bmargin 2
set xtics nomirror
set ytics nomirror

set title \"Evolutie rating pentru {$user['username']} (".IA_URL.")\" 0,-.5

set style line 1 lt 1 lw 4 pt 3 ps 0.5
set style line 2 lt 3 lw 3 pt 7 ps 1.0
set style line 3 lt 11 lw 3
set xrange [\"{$range_start}\":\"{$range_end}\"]
set yrange [{$rating_start}:{$rating_end}]

set xtics ('".join("', '", $xtics)."')
set xtic rotate by -20

set clip
";

// display round_id labels
$i = 1;
$scale = ceil(($rating_end-$rating_start)/20);
foreach ($history as $round_id => $round) {
    $date = date("Y-m-d", $round['timestamp']);
    $rating = rating_scale($round['rating']) + ($i % 2 ? $scale : -$scale);
    $align = "right";
    $script .= "set label {$i} \"{$i}\" at \"{$date}\",{$rating} {$align} font \"Helvetica,19\" back tc lt 9\n";
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
    \"%data%\" using 1:2 notitle with points ls 2
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
include(IA_ROOT_DIR.'www/views/gnuplot.php');

?>
