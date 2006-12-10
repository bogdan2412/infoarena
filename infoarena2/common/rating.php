<?php

// infoarena rating system
//
// In laymen terms, rating systems provide a way to rank and differentiate
// contestants in multiple-round competitions. Ratings are computed with
// black math & statistics voodoo magic :)
//
// infoarena uses home-baked rating system combining features from
// glicko, TrueSkill, ELO and possibly others.
//
// See development documentation on ratings:
// http://hackers.devnet.ro/wiki/Rating

// Glicko parameters
// These may be tweaked until they yield decent ratings

// Initial rating assumed for a new contestant.
// Note that we're using standard ELO ratings (spanning 3000 and beyond)
define("IA_RATING_INITIAL", 1500);
// Initial deviation
define("IA_RATING_DEVIATION", 99);
// Deviation boundaries and median
define("IA_RATING_MAX_DEVIATION", 360);
define("IA_RATING_MIN_DEVIATION", 15);
define("IA_RATING_MED_DEVIATION", 45);
// threshold value to normalize results
// FIXME: Ratings don't change more than this
define("IA_RATING_MAX_DIFF", 300);
// number of seconds in a time period
define("IA_RATING_TIME_PERIOD", 2628000);
// number of months before you reach maximum unreliability
// 4 years now
define("IA_RATING_CHAOS", 48);
// don't ask.
// FIXME: But I do! What do these mean?
define("IA_RATING_C", sqrt((IA_RATING_MAX_DEVIATION * IA_RATING_MAX_DEVIATION - IA_RATING_MED_DEVIATION * IA_RATING_MED_DEVIATION) / IA_RATING_CHAOS));
define("IA_RATING_Q", log(10.0) / 400.0);
// we're feeding games into chunks and periodically updating ratings
define("IA_RATING_MAX_CHUNK", 10);
// tweak rating increases to avoid unusual behavior for huge contents 
define("IA_RATING_TWEAK_PERIOD", 3);

// number square
function sqr($number) {
    return $number * $number;
}

// init user array where we store and update ratings
// $whole_user_list array format is (list of usernames):
//  array(
//      username,
//      ...
//  );
// $ratings array is as returned by rating_last_scores()
// Output array format is:
//  array(
//      username => array(
//                      rating => (int),
//                      deviation => (int),
//                      timestamp => (int)
//                  )
//      ...
//  );
function rating_init($whole_user_list, $last_scores) {
    $users = $last_scores;
    foreach ($whole_user_list as $username) {
        if (isset($users[$username])) {
            continue;
        }

        $user = array(
            'rating' => IA_RATING_INITIAL,
            'deviation' => IA_RATING_DEVIATION,
            'timestamp' => 0,
        );
        $users[$username] = $user;
    }
    return $users;
}

// update user deviation. consider now is $timestamp
// deviations must be update before updating ratings
//
// $timestamp is UNIX timestamp (seconds)
function rating_update_deviation(&$users, $username, $timestamp) {
    log_assert(isset($users[$username]));

    $user =& $users[$username];
    $old_deviation = $user['deviation'];
    if (!$user['timestamp']) {
        // user never was rated, leave deviation as it is
        log_assert(IA_RATING_DEVIATION == $user['deviation']);
        $elapsed = 0;
    }
    else {
        // compute elapsed time from the last rating to current timestamp
        $elapsed = (int)ceil(($timestamp - $user['timestamp']) / IA_RATING_TIME_PERIOD);
    }
    $user['deviation'] = min(IA_RATING_MAX_DEVIATION,
                             round(sqrt(sqr($old_deviation) + sqr(IA_RATING_C) * $elapsed)));
    $user['timestamp'] = $timestamp;
}

// G Spot baby :)
function rating_gspot($deviation) {
    $deviation = 1.0 / sqrt(1.0 + 3.0 * sqr(IA_RATING_Q) * sqr($deviation) / sqr(M_PI));
    return $deviation;
}

// Expected score for a pair of contestants
function rating_expected_score($rating1, $rating2, $deviation2) {
    $diff = max(-IA_RATING_MAX_DIFF, min(IA_RATING_MAX_DIFF, $rating1 - $rating2));
    $score = 1.0 / (1.0 + pow(10, -rating_gspot($deviation2) * $diff / 400.0));
    return $score;
}

// FIXME: Throw in some comments
function rating_score($score1, $score2, $variance) {
    if ($score1 > $score2) {
        if (!$score2) {
           return 1.0;
        }
        return 0.5 + min(0.5, ($score1 - $score2) / $variance * 0.25);
    }

    if ($score1 < $score2) {
        if (!$score1) {
            return 0.0;
        }
        return 0.5 - min(0.5, ($score2 - $score1) / $variance * 0.25);
    }
    return 0.5;
}

// Update ratings considering user_scores. Assume now is $timestamp.
// $timestamp is UNIX timestamp
// FIXME: Throw in some comments
//
// $user_scores array format is:
//  array(
//      username => score,
//      ...
//  )
function rating_update(&$users, $user_scores, $timestamp) {
    log_assert(1 < count($user_scores), "Need more than 1 contestant to "
                                        ."update ratings!");
    log_print("Updating ratings for ".count($user_scores)." users");

    $new_rating = array();
    $new_deviation = array();

    // update deviations and compute score mean
    $score_mean = 0;
    foreach ($user_scores as $username => $score) {
        rating_update_deviation($users, $username, $timestamp);
        $score_mean += $score;
    }
    $score_mean /= count($user_scores);

    // compute score variance
    $score_variance = 0;
    foreach ($user_scores as $username => $score) {
        $score_variance += sqr($score - $score_mean);
    }
    $score_variance = sqrt($score_variance / count($user_scores));

    // Voodoo Magic
    // FIXME: Throw in some comments if you understand anything about this
    $user_count = count($user_scores);
    foreach ($user_scores as $username => $score) {
        log_assert(isset($users[$username]));
        $user =& $users[$username];

        log_assert(isset($user['rating']) && isset($user['deviation'])
                   && isset($user['timestamp']));


        $rating1 = $user['rating'];
        $deviation1 = $user['deviation'];
        $pos = 0;
        $keys = array_keys($user_scores);
        $chunk_count = ceil(($user_count-1) / IA_RATING_MAX_CHUNK);
        $tweak = $chunk_count / IA_RATING_TWEAK_PERIOD;

        for ($chunk = 0; $chunk < $chunk_count; $chunk++, $pos += IA_RATING_MAX_CHUNK) {
            $D = 0.0;
            for ($i = $pos; $i < $pos+IA_RATING_MAX_CHUNK && $i < $user_count; $i++) {
                $username2 = $keys[$i];
                $score2 = $user_scores[$username2];
                log_assert(isset($users[$username2]));
                $user2 = $users[$username2];
                if ($username2 == $username) {
                    continue;
                }
                $rating2 = $user2['rating'];
                $deviation2 = $user2['deviation'];

                $temp = rating_expected_score($rating1, $rating2, $deviation2);
                $D = $D + sqr(rating_gspot($deviation2)) * $temp * (1.0 - $temp);
            }
            $D = $D * sqr(IA_RATING_Q);

            $R = 0.0;
            for ($i = $pos; $i < $pos+IA_RATING_MAX_CHUNK && $i < $user_count; $i++) {
                $username2 = $keys[$i];
                $score2 = $user_scores[$username2];
                log_assert(isset($users[$username2]));
                $user2 = $users[$username2]; 
                if ($username2 == $username) {
                    continue;
                }
                $rating2 = $user2['rating'];
                $deviation2 = $user2['deviation'];

                $temp = rating_expected_score($rating1, $rating2, $deviation2);
                $weight = max(IA_RATING_Q * rating_gspot($deviation2) * sqr(IA_RATING_MIN_DEVIATION),
                              IA_RATING_Q * rating_gspot($deviation2) / (1.0 / sqr($deviation1) + $D));
                $R = $R + $weight * (rating_score($score, $score2, $score_variance) - $temp);
            }
        }

        // update rating and deviation
        $user['rating'] = max(0, round($rating1 + $R / $tweak));
        $user['deviation'] = max(IA_RATING_MIN_DEVIATION,
                                 round(sqrt(1.0 / (1.0 / sqr($deviation1) + $D))));
    }
}

// Represent rating in a human-friendly scale from 0 to 1000
// NOTE: This is used only when displaying ratings to users!
function rating_scale($absolute_rating) {
    log_assert(is_numeric($absolute_rating));
    return round($absolute_rating / 3.0);
}

// Return rating group based on user's absolute rating.
// Rating groups (from highest to lowest ranking): 1, 2, 3, 0
// NOTE: It outputs 0 when user is not rated
function rating_group($absolute_rating) {
    if (!$absolute_rating) {
        return 0;
    }

    $rating = rating_scale($absolute_rating);

    if ($rating < 400) {
        return 3;
    }
    else if ($rating < 700) {
        return 2;
    }
    else {
        return 1;
    }
}

?>
