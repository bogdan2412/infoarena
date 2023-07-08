<?php

require_once(Config::ROOT."common/db/score.php");
require_once(Config::ROOT."common/db/user.php");
require_once(Config::ROOT."www/format/list.php");
require_once(Config::ROOT."common/db/round.php");

// Display rounds that user has participated in.
//
// Optionally, it can display only rounds that affect user rating.
// When displaying only rating rounds, it shows an order-list instead of an
// un-order one so we can use it a legend to the rating history plot.
function macro_rounds($args) {
    $username = getattr($args, 'user');
    $only_rated = getattr($args, 'only_rated', false);

    if (!$username) {
        return macro_error("Expecting argument `user`");
    }

    // validate user
    $user = user_get_by_username($username);
    if (!$user) {
        return macro_error("Nu such username: ".$username);
    }

    // get round list
    if ($only_rated) {
        $rounds = rating_history($user['id']);
    }
    else {
        $rounds = user_submitted_rounds($user['id']);
    }

    // view
    if (1 <= count($rounds)) {
        $urls = array();
        if ($only_rated) {
            foreach ($rounds as $row) {
                $urls[] = format_link(
                        url_textblock($row['round_page_name']),
                        $row['round_title']);
            }
            return format_ol($urls);
        }
        else {
            foreach ($rounds as $row) {
                $urls[] = format_link(url_textblock($row['page_name']), $row['title']);
            }
            return format_ul($urls);
        }
    }
    else {
        // no rated rounds
        return '<em>niciun concurs</em>';
    }
}

?>
