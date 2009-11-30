<?php

require_once(IA_ROOT_DIR."www/php-ofc-library/open-flash-chart.php");

// get data ranges
if (2 <= count($history)) {
    $keys = array_keys($history);

    $range_start = floor($history[$keys[0]]['timestamp'] / (24 * 3600));
    $range_start -= (int)date('d', $history[$keys[0]]['timestamp']) - 1;
    $range_end = ceil($history[$keys[count($history) - 1]]['timestamp'] / (24 * 3600)) + 10;

    $rating_start = $history[$keys[0]]['rating'];
    $rating_end = $rating_start;
    foreach ($history as $round_id => $round) {
        $rating_start = min($rating_start, $round['rating']);
        $rating_end = max($rating_end, $round['rating']);
    }
    $rating_start = rating_scale($rating_start) - 50;
    $rating_start -= $rating_start % 50;
    $peak_point = rating_scale($rating_end);
    $rating_end = $peak_point + 50;
} else {
    // 2004-01-01
    $range_start = floor(mktime(1, 0, 0, 1, 1, 2004) / (24 * 3600));
    $range_end = floor(time() / (24 * 3600));
    $rating_start = 0;
    $rating_end = $peak_point = 1000;
}

// create the rating chart
$chart = new open_flash_chart();

$chart->set_bg_colour("#ffffff");

$title = new title("Evolutia ratingului pentru " . $user['username']);
$chart->set_title($title);

// adjust axises and labels
$x = new x_axis();
$x->set_colour("#aaaaaa");
$x->set_grid_colour("#aaaaaa");
$x->set_range($range_start, $range_end);
$x->set_steps(122);

$labels = array();
for ($p = $range_start; $p <= $range_end; ++$p) {
    $labels[] = date("d-m-Y", $p * 24 * 3600);
}

$x_axis_labels = new x_axis_labels();
$x_axis_labels->set_labels($labels);
$x_axis_labels->set_vertical();
$x_axis_labels->set_steps(122);

$x->set_labels($x_axis_labels);
$chart->set_x_axis($x);

$y = new y_axis();
$y->set_colour("#aaaaaa");
$y->set_grid_colour("#aaaaaa");
$y->set_range($rating_start, $rating_end);
$y->set_steps(50);
$chart->set_y_axis($y);

// generate the rating chart
$data = array();

$p = $range_start;
foreach ($history as $round_id => $round) {
    $current_x = floor($round['timestamp'] / (24 * 3600));
    for (; $p < $current_x; ++$p) {
        $data[] = null;
    }

    $current_y = rating_scale($round['rating']);
    if ($current_y < 520) {
        $colour = "#00a900";
    } elseif ($current_y < 600) {
        $colour = "#ddcc00";
    } else {
        $colour = "#ee0000";
    }
    $dot = new dot_value($current_y, $colour);
    if ($current_y == $peak_point) {
        $dot->set_tooltip("Max: #val#<br>" . $round['round_title']);
    } else {
        $dot->set_tooltip("#val#<br>" . $round['round_title']);
    }

    $data[] = $dot;
    ++$p;
}

$line_dot = new line_dot();
$line_dot->set_dot_size(4);
$line_dot->set_halo_size(1);
$line_dot->set_values($data);
$chart->add_element($line_dot);

// output data
echo $chart->toString();

?>
