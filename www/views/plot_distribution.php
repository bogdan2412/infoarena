<?php

require_once(IA_ROOT_DIR."www/format/format.php");
require_once(IA_ROOT_DIR."www/php-ofc-library/open-flash-chart.php");

// create the rating chart
$chart = new open_flash_chart();

$chart->set_bg_colour("#ffffff");

$title = new title("Distribuția ratingului");
$chart->set_title($title);

// adjust axises and labels
$x = new x_axis();
$x->set_colour("#aaaaaa");
$x->set_grid_colour("#ffffff");

$first_element = array_slice($distribution, 0, 1, TRUE);
foreach ($first_element as $first_bucket => $count) {
    $p = $first_bucket;
}

$labels = array();
foreach ($distribution as $bucket => $count) {
    for (; $p < $bucket; ++$p) {
        $current_rating = rating_scale($p * $bucket_size);
        $current_label = $current_rating . " - " . ($current_rating + rating_scale($bucket_size) - 1);
        $labels[] = $current_label;
    }

    $current_rating = rating_scale($bucket * $bucket_size);
    $current_label = $current_rating . " - " . ($current_rating + rating_scale($bucket_size) - 1);
    $labels[] = $current_label;

    ++$p;
}
$x_axis_labels = new x_axis_labels();
$x_axis_labels->set_vertical();
$x_axis_labels->set_labels($labels);
$x->set_labels($x_axis_labels);

$chart->set_x_axis($x);

$y = new y_axis();
$y->set_colour("#aaaaaa");
$y->set_grid_colour("#aaaaaa");
$y->set_range(0, max($distribution) * 1.15);
$y->set_steps(50);
$chart->set_y_axis($y);

// generate the rating chart
$bar = new bar_filled();

foreach ($first_element as $first_bucket => $count) {
    $p = $first_bucket;
}
foreach ($distribution as $bucket => $count) {
    for (; $p < $bucket; ++$p) {
        $bar_value = new bar_value(0);
        $current_rating = rating_scale($p * $bucket_size);
        $bar_value->set_tooltip("Rating: " . $current_rating . " - " .
            ($current_rating + rating_scale($bucket_size) - 1)
            . "<br>0 concurenți");
        $bar->append_value($bar_value);
    }

    $bar_value = new bar_value($count);
    $current_rating = rating_scale($bucket * $bucket_size);
    $bar_value->set_tooltip("Rating: " . $current_rating . " - " .
        ($current_rating + rating_scale($bucket_size) - 1)
        . "<br>" . $count . ($count == 1 ? " concurent" : " concurenți"));

    $rating_group = rating_group(rating_scale($bucket * $bucket_size));
    $bar_value->set_colour($rating_group["colour"]);
    $bar->append_value($bar_value);
    ++$p;
}

$chart->add_element($bar);

// mark the rating of the current user
$user_rating = rating_scale(getattr($user, 'rating_cache', 0));
foreach ($first_element as $first_bucket => $count) {
    $x_coord = ($user_rating - rating_scale($first_bucket * $bucket_size + $bucket_size / 2.)) / rating_scale($bucket_size);
}

$shape = new shape("#0000ff");
$shape->append_value(new shape_point($x_coord - 0.05, 0));
$shape->append_value(new shape_point($x_coord + 0.05, 0));
$shape->append_value(new shape_point($x_coord + 0.05, max($distribution) * 1.15));
$shape->append_value(new shape_point($x_coord - 0.05, max($distribution) * 1.15));
$chart->add_element($shape);

// output data
echo $chart->toString();

?>
