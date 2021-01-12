<?php

// email routines

// Word-wrap to avoid some problems with bad email clients
define("IA_EMAIL_WORDRAP", 72);

// if SMTP is enabled, feed some settings to PHP
if (IA_SMTP_ENABLED) {
    ini_set("SMTP", IA_SMTP_HOST);
    ini_set("smtp_port", IA_SMTP_PORT);
}

// Sends text/plain email message. This wraps standard PHP email functions.
// Default From: is IA_MAIL_SENDER_NO_REPLY
function send_email($to, $subject, $plain_text_message, $from = null,
                    $reply_to = null, $do_log = false)
{
    if (is_null($from)) {
        $from = IA_MAIL_SENDER_NO_REPLY;
    }

    // if we don't specify reply-to, should be the same as the from
    if (is_null($reply_to)) {
        $reply_to = $from;
    }

    $subject = SITE_NAME . ': ' . $subject;

    // word-wrap message, some mail-clients are stupid
    $message = wordwrap($plain_text_message, IA_EMAIL_WORDRAP);

    // headers
    $headers = 'From: ' . $from . "\n" .
               'Reply-To: ' . $reply_to . "\n" .
               "Content-type: text/plain\n" .
               'X-Mailer: ' . SITE_NAME . '/newsletter';

    // log
    if ($do_log) {
        log_print("Sending mail to: {$to}, subject: {$subject}, "
                  ."message length: ".strlen($message));
    }

    // send e-mail
    return mail($to, $subject, $message, $headers);
}

?>
