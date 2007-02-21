<?php

require_once(IA_ROOT_DIR."www/format/format.php");

if ($user) {
    $user_rating = $user['rating_cache'];
    if ($user_rating) {
        $user_rating = rating_scale($user_rating);
    }
}
else {
    $user_rating = null;
}

// gnuplot script
$script = "
set grid

set rmargin 2
set lmargin 5
set tmargin 12
set bmargin 2
set xtics nomirror
set ytics nomirror

set title \"Distributie rating (".IA_URL.")\" 0,-.5

set style line 1 lt 1 lw 4 pt 3 ps 0.5
set style line 2 lt 7 lw 6 pt 7 ps 1.0
set style line 3 lt 11 lw 3
set xrange [350:750]

set xtics 50
set xtic rotate by -35

";

// legend
$script .= "
set key left top box 3
set key width -1.5
";

// draw user rating as a parametric curve with constant x
if ($user && $user_rating) {
    $script .= "
set parametric
const={$user_rating}
set trange [0:100]
";
}

// plot distribution & median
$script .= "
plot \\
    \"%data%\" using 1:2 title \"Concurenti\" with histeps ls 3, \\
    \"%data%\" using 1:2 smooth bezier title \"Aproximare\" with lines ls 1";

// plot user
if ($user && $user_rating) {
    $script .= ", \\
    const,t title \"{$user['username']} ({$user_rating})\" with lines ls 2
";
}

// plot data
// #rating bucket #count
$data = '';
foreach ($distribution as $bucket => $count) {
    $scaled = rating_scale($bucket*$bucket_size);
    $data .= $scaled." ".$count."\n";
}

// render PNG
include(IA_ROOT_DIR.'www/views/gnuplot.php');
?>
