<?php

require_once(Config::ROOT."common/db/task_rating.php");
require_once(Config::ROOT."common/rating.php");

// Computes the rating out of the array $ratings
// $ratings contains arrays of ratings
function task_rating_compute($ratings) {
    $idea = 0.0;
    $theory = 0.0;
    $coding = 0.0;

    // Compute average ratings for each category
    foreach ($ratings as $rating) {
        $idea += $rating['idea'];
        $theory += $rating['theory'];
        $coding += $rating['coding'];
    }
    $idea /= count($ratings);
    $theory /= count($ratings);
    $coding /= count($ratings);

    $best = max($idea, $theory, $coding);

    // Compute a weighted sum of the three ratings
    $weight_idea = sqr($idea / $best);
    $weight_theory = sqr($theory / $best);
    $weight_coding = sqr($coding / $best);

    $final_grade = ($weight_idea * $idea +
                    $weight_theory * $theory +
                    $weight_coding * $coding) /
                   ($weight_idea + $weight_theory + $weight_coding);

    // Find proper difficulty number
    $cut_offs = array(3.40, 4.00, 5.00, 6.00, 10.00);
    for ($i = 0; $i < count($cut_offs); ++$i)
      if ($cut_offs[$i] >= $final_grade)
        return $i + 1;
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
