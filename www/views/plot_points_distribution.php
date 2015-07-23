<?php

require_once IA_ROOT_DIR.'www/format/format.php';
require_once IA_ROOT_DIR.'www/php-ofc-library/open-flash-chart.php';

$points_distribution = $view['points_distribution'];
$max_count = max($points_distribution);

// create the points chart
$chart = new open_flash_chart();

$chart->set_bg_colour('#ffffff');

$title = new title('Distributia punctajelor');
$chart->set_title($title);

// adjust axises and labels
$x = new x_axis();
$x->set_colour('#aaaaaa');
$x->set_grid_colour('#ffffff');

$labels = array();
foreach ($points_distribution as $score => $count) {
    $labels[] = (string) $score;
}

$x_axis_labels = new x_axis_labels();
$x_axis_labels->set_labels($labels);
$x->set_labels($x_axis_labels);

$chart->set_x_axis($x);

$y = new y_axis();
$y->set_colour('#aaaaaa');
$y->set_grid_colour('#aaaaaa');
$y->set_range(0, $max_count);
$y->set_steps($max_count);
$chart->set_y_axis($y);

// generate the points chart
$bar = new bar_filled();

foreach ($points_distribution as $score => $count) {
    $bar_value = new bar_value($count);
    $bar_value->set_tooltip("Punctaj $score<br>$count concurent".($count != 1 ? 'i' : ''));
    $bar->append_value($bar_value);
}

$chart->add_element($bar);

// mark the points of the current user
if (array_key_exists('user_points', $view) && !is_null($view['user_points'])) {
    $user_points = $view['user_points'];
    $offset = 0;
    foreach ($points_distribution as $score => $count) {
        if ($user_points == $score) {
            break;
        }
        $offset++;
    }
    $x_coord = $offset;

    $shape = new shape('#ff0000');
    $shape->append_value(new shape_point($x_coord - 0.05, 0));
    $shape->append_value(new shape_point($x_coord + 0.05, 0));
    $shape->append_value(new shape_point($x_coord + 0.05, $max_count + 1));
    $shape->append_value(new shape_point($x_coord - 0.05, $max_count + 1));
    $chart->add_element($shape);
}

// output data
echo $chart->toString();
