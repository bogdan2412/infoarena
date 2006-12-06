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

require_once(IA_ROOT.'common/log.php');

// Glicko parameters
// These may be tweaked until they yield decent ratings

// Initial rating assumed for a new contestant.
// Note that we're using standard ELO ratings (spanning 3000 and beyond)
define("IA_RATING_INITIAL", 1500);
// Initial deviation
define("IA_RATING_DEVIATION", 100);
// Deviation boundaries and median
define("IA_RATING_MAX_DEVIATION", 350);
define("IA_RATING_MIN_DEVIATION", 20);
define("IA_RATING_MED_DEVIATION", 50);
// threshold value to normalize results
// FIXME: Ratings don't change more than this
define("IA_RATING_MAX_DIFF", 270);
// number of seconds in a time period
define("IA_RATING_TIME_PERIOD", 2628000);
// number of months before you reach maximum unreliability
// 4 years now
define("IA_RATING_CHAOS", 48);
// don't ask.
// FIXME: But I do! What do these mean?
define("IA_RATING_C", sqrt((IA_RATING_MAX_DEVIATION * IA_RATING_MAX_DEVIATION - IA_RATING_MED_DEVIATION * IA_RATING_MED_DEVIATION) / IA_RATING_CHAOS));
define("IA_RATING_Q", log(10.0) / 400.0);

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
        $elapsed = (int)floor(($timestamp - $user['timestamp']) / IA_RATING_TIME_PERIOD);
    }
    $user['deviation'] = min(IA_RATING_MAX_DEVIATION,
                             round(sqrt(sqr($old_deviation) + sqr(IA_RATING_C) * $elapsed)));
    $user['timestamp'] = $timestamp;
}

// FIXME: Throw in some comments
function rating_gspot($deviation) {
    $deviation = 1.0 / sqrt(1.0 + 3.0 * sqr(IA_RATING_Q) * sqr($deviation) / sqr(M_PI));
    return $deviation;
}

// FIXME: Throw in some comments
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

    // Voodoo
    // FIXME: Throw in some comments if you understand anything about this
    foreach ($user_scores as $username => $score) {
        log_assert(isset($users[$username]));
        $user =& $users[$username];

        log_assert(isset($user['rating']) && isset($user['deviation'])
                   && isset($user['timestamp']));

        $rating_i = $user['rating'];
        $deviation_i = $user['deviation'];

        $D = 0.0;
        foreach ($user_scores as $username2 => $score2) {
            log_assert(isset($users[$username2]));
            $user2 = $users[$username2];
            if ($username2 == $username) {
                continue;
            }
            $rating_j = $user2['rating'];
            $deviation_j = $user2['deviation'];

            $temp = rating_expected_score($rating_i, $rating_j, $deviation_j);
            $D = $D + sqr(rating_gspot($deviation_j)) * $temp * (1.0 - $temp);
        }
        $D = $D * sqr(IA_RATING_Q);

        $R = 0.0;
        foreach ($user_scores as $username2 => $score2) {
            log_assert(isset($users[$username2]));
            $user2 = $users[$username2];
            if ($username2 == $username) {
                continue;
            }
            $rating_j = $user2['rating'];
            $deviation_j = $user2['deviation'];

            $temp = rating_expected_score($rating_i, $rating_j, $deviation_j);
            $weight = max(IA_RATING_Q * rating_gspot($deviation_j) * sqr(IA_RATING_MIN_DEVIATION),
                          IA_RATING_Q * rating_gspot($deviation_j) / (1.0 / sqr($deviation_i) + $D));
            $R = $R + $weight * (rating_score($score, $score2, $score_variance) - $temp);
        }

        // update rating and deviation
        $user['rating'] = max(0, round($rating_i + $R));
        $user['deviation'] = max(IA_RATING_MIN_DEVIATION,
                                 round(sqrt(1.0 / (1.0 / sqr($deviation_i) + $D))));
    }
}

// Represent rating in a human-friendly scale from 0 to 1000
// NOTE: This is used only when displaying ratings to users!
function rating_scale($rating) {
    log_assert(is_numeric($rating));
    return (int)round($rating / 3);
}

?>
