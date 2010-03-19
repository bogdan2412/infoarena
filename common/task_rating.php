<?php

require_once(IA_ROOT_DIR."common/db/task_rating.php");

// Computes the rating out of the array $ratings
// $ratings contains arrays of ratings
// FIXME: use smarter task rating function
function task_rating_compute($ratings) {
    $sum = 0;
    $nr = 0;

    foreach ($ratings as $rating) {
        $sum += $rating['idea'] + $rating['theory'] + $rating['coding'];
        $nr += 3;
    }

    $task_rating = 1.0 * $sum / $nr;

    return $task_rating;
}

// Checks to see if a value is an int between 1 and 10.
function task_is_rating_value($rating_value) {
    if (!is_whole_number($rating_value)) {
        return false;
    }

    $int_rating_value = intval($rating_value);
    if ($int_rating_value < 1 || $int_rating_value > 10) {
        return false;
    }

    return true;
}

?>
