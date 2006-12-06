<?php

require_once(IA_ROOT."common/db/score.php");
require_once(IA_ROOT."common/rating.php");

// Display rounds that affected user rating. Keep the same numbers
// as displayed in the rating history graph.

function macro_ratingrounds($args) {
    $username = getattr($args, 'user');
    if (!$username) {
        return macro_error("Expecting argument `user`");
    }

    // validate user
    $user = user_get_by_username($username);
    if (!$user) {
        return macro_error("Nu such username: ".$username);
    }

    log_print("Plotting rating history of user ".$username);

    // get rating history
    $history = rating_history($user['id']);

    // view
    if (1 <= count($history)) {
        $buffer = "";
        $i = 1;
        foreach ($history as $round_id => $round) {
            if ($buffer) {
                $buffer .= ", ";
            }
            $buffer .= $i.". ".format_link(url($round['round_page_name']), $round_id);
            $i++;
        }
        return $buffer;
    }
    else {
        // no rated contests
        return "";
    }
}

?>
