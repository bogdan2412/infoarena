<?php

require_once(IA_ROOT_DIR . 'common/db/db.php');
require_once(IA_ROOT_DIR . 'common/textblock.php');
require_once(IA_ROOT_DIR . 'common/user.php');
require_once(IA_ROOT_DIR . 'lib/Wiki.php');
require_once(IA_ROOT_DIR . 'www/url.php');

// Render HTML newsletter for recipient $user, from given $textblock.
// The newsletter is personalized for the recipient by doing a
// textblock_template_replace(...) for %username% tags.
function newsletter_body_html($textblock, $user, $in_browser = false) {
    log_assert_valid(textblock_validate($textblock));
    $user_is_anonymous = !$user;
    if ($user_is_anonymous) {
        // Anonymous user. Build fake user object.
        $user = newsletter_anonymous_user();
    }
    log_assert_valid(user_validate($user));

    // Personalize newsletter and process textile.

    $replace = array("username" => $user['username']);
    textblock_template_replace($textblock, $replace);
    $body_html = Wiki::processText($textblock['text']);
    $subject = newsletter_subject($textblock, $user);

    // Generate HTML using newsletter template.

    ob_start();
    include(IA_ROOT_DIR . "common/newsletter_template.php");
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
}

// Alternate body in text/plain format. This should be displayed by email
// clients that don't support HTML.
function newsletter_body_alternate($textblock, $user) {
    log_assert_valid(user_validate($user));
    log_assert_valid(textblock_validate($textblock));
    $unsubscribe_url = url_absolute(url_unsubscribe($user['username'],
            user_unsubscribe_key($user)));
    return join("", array(
        "Acest email este redactat în format HTML. Dacă nu se afișează\n",
        "corect, te rog să îl vizualizezi în browser la adresa:\n\n",
        url_absolute(url_newsletter($textblock['name'])), "\n\n\n",
        "Ai primit acest mesaj deoarece ești înscris pe ",
        url_absolute(url_home()), "\n", "cu numele \"{$user['full_name']}\", ",
        "utilizator \"{$user['username']}\",\n",
        "adresa de email \"{$user['email']}\".\n\n",
        "Dacă nu mai dorești să primești astfel de mesaje, te poți dezabona\n",
        "acum la adresa:\n\n", $unsubscribe_url));
}

// Generate newsletter subject for recipient $user.
function newsletter_subject($textblock, $user) {
    log_assert_valid(textblock_validate($textblock));
    if (!$user) {
        // Anonymous user. Build fake user object.
        $user = newsletter_anonymous_user();
    }
    log_assert_valid(user_validate($user));
    return 'infoarena: '.$textblock['title'];
}

// Fake, anonymous recipient.
function newsletter_anonymous_user() {
    $user = user_init();
    $user['username'] = 'anonymous';
    $user['password'] = '*';
    $user['full_name'] = 'Utilizator infoarena';
    $user['email'] = 'utilizator-infoarena@example.com';
    $user['newsletter'] = '1';
    return $user;
}

?>
