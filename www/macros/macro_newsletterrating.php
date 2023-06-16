<?php

require_once(IA_ROOT_DIR."common/db/user.php");

// Displays a "Your rating is ..." paragraph for given username, only if the
// user actually has rating.
//
// Example:
//      ==NewsletterRating(username="wickedman")==
function macro_newsletterrating($args) {
    $username = getattr($args, 'username', '');
    if ($username === '') {
        return macro_error("Nu ați specificat numele utilizatorului.");
    }
    $user = user_get_by_username($username);
    if (!$user) {
        return macro_error("Utilizatorul {$username} nu există.");
    }
    if ((int)$user['rating_cache']) {
        $rating_url = url_user_rating($user['username']);
        $rating = rating_scale($user['rating_cache']);
        return '<p>Rating-ul tău este '
                .'<a href="'.html_escape($rating_url).'">'
                .html_escape($rating).'</a>.</p>';
    } else {
        return '';
    }
}
?>
