<?php

/* Wrapper over PHPMailer with SMTP settings taken from Config.php */

require_once 'third-party/PHPMailer-6.9.1/Exception.php';
require_once 'third-party/PHPMailer-6.9.1/PHPMailer.php';
require_once 'third-party/PHPMailer-6.9.1/SMTP.php';

class Mailer {

  /**
   * $from: from address; should have corresponding credentials in the config file
   * $to: array of recipient addresses
   * $subject: subject line
   * $textBody: plain text body
   * $htmlBody: HTML body (optional)
   **/
  static function send($from, $to, $subject, $textBody, $htmlBody = null) {
    $info = self::getInfo($from);

    // set the from, to and subject fields
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setFrom($from, $info['name']);
    foreach ($to as $recipient) {
      $mail->addAddress($recipient);
    }
    $mail->Subject = $subject;

    // set the plaintext and/or html body
    if ($htmlBody) {
      $mail->Body    = $htmlBody;
      $mail->AltBody = $textBody;
    } else {
      $mail->Body    = $textBody;
    }

    // configure SMTP
    $mail->isSMTP();
    $mail->Host = Config::SMTP_SERVER;
    $mail->Username = $info['username'];
    $mail->Password = $info['password'];
    $mail->SMTPAuth = true;

    $mail->SMTPOptions = [
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true,
      ],
    ];

    // ship it!
    if (Config::SMTP_ENABLED) {
      $mail->send();
    } else {
      $mail->Encoding = '8-bit';
      $mail->preSend();
      print $mail->getSentMIMEMessage();
      print $mail->Body;
    }
  }

  /**
   * Returns the name and SMTP password for this address. Throws an exception
   * if the values are undefined.
   **/
  static function getInfo($from) {
    $identity = Config::EMAIL_IDENTITIES[$from] ?? null;

    if (!$identity) {
      throw new Exception('No email identity found for ' . $from);
    }

    return $identity;
  }
}
