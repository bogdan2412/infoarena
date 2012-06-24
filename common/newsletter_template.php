<?php

// Writing HTML email is painful. Most of the popular email clients impose
// severe restrictions on your HTML and CSS code.
//
// At the time of writing this, Gmail strips all <style> tags, and all DOM IDs
// and CSS class names. This leaves us only with (repetitive) inline CSS.
// Don't rely on images showing up by default. Yahoo! Webmail blocks all
// images, including those embedded (i.e., attached) to the message.
// Tables seem to be the only reliable way of doing layouts.
// Check http://www.email-standards.org/ for more guide lines.
//
// If you plan on changing this template, please make sure to test it
// at least in Yahoo! Webmail, Gmail, and Hotmail. At the time of writing
// this (2009-01-19) they account for 87% of all our subscribers.

log_assert_valid(user_validate($user));
log_assert_valid(textblock_validate($textblock));
log_assert(isset($body_html));
log_assert(isset($subject));
log_assert(isset($in_browser));
log_assert(isset($user_is_anonymous));

?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <?php if ($in_browser) { ?>
    <script type="text/javascript" src="<?= html_escape(url_static('js/config.js.php')) ?>"></script>
    <script type="text/javascript" src="<?= html_escape(IA_DEVELOPMENT_MODE?url_static('js/jquery-1.7.2.js'):'//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js') ?>"></script>
    <script type="text/javascript" src="<?= html_escape(url_static('js/newsletter.js')) ?>"></script>
  <?php } ?>
</head>
<body>
  <p>&nbsp;</p>

  <table width="100%" cellpadding="10" cellspacing="0" border="0" style="border:0">
    <tr><td valign="top" align="center" border="0" style="border:0">

    <table width="550" cellpadding="0" cellspacing="0" class="layoutHeader" style="width:500px; border:2px solid #d9ffbe" border="0">
      <?php if (!$in_browser) { ?>
        <tr><td style="background-color:#d9ffbe; text-align:left; border:0; padding:3px" align="left"
            border="0" cellpadding="3" bgcolor="#dnffbe">
          <span style="font-size:8pt; line-height:200%; color:#006000; font-family:sans-serif; text-decoration:none;">
              Dacă acest email nu se afişează corect, te rugăm să îl
              <a href="<?= html_escape(url_absolute(url_textblock($textblock['name']))) ?>"
                    style="color:#006000; text-decoration:underline;">vizualizezi în browser</a>.</span>
        </td></tr>
      <?php } else { ?>
        <tr><td style="background-color:#d9ffbe; text-align:left; border:0" align="left" border="0" bgcolor="#dnffbe">
          <span style="font-size:8pt; line-height:200%; color:#006000; font-family:sans-serif; text-decoration:none;">
            <strong>Subiect:</strong>
            <?= html_escape($subject) ?><br/>
            <?php if (is_db_date($textblock['timestamp'])) { ?>
              <strong>Data:</strong>
              <?= html_escape(strftime('%Y-%m-%d', db_date_parse($textblock['creation_timestamp']))) ?><br/>
            <?php } ?>
          </span>
        </td></tr>
      <?php } ?>
      <tr><td style="vertical-align:middle; padding:15pt 10pt 10pt 10pt; border:0; text-align:left" align="left" valign="middle" border="0" cellpadding="10">
        <a href="<?= html_escape(url_absolute(url_home())) ?>" style="font-size:25pt; color:#4a8f19; font-family:sans-serif; text-decoration:none;">info<em><i>arena</i></em></a>
      </td></tr>

      <tr><td valign="top" style="font-size:10pt; color:#000000; line-height:150%; font-family:sans-serif; border:0; padding:5pt 15pt 15pt 15pt; text-align:left" cellpadding="15" border="0" align="left">
        <?= $body_html ?>
      </td></tr>

      <tr><td style="background-color:#d0ffbe; text-align:left; border:0; padding:3px"
            valign="top" align="left" border="0" cellpadding="3">
        <div style="font-size:8pt; color:#006000; line-height:150%; font-family:sans-serif;">

        <?php if (!$in_browser) { ?>

        <p>Ai primit acest mesaj deoarece eşti înscris pe
          <a href="<?= html_escape(url_absolute(url_home())) ?>" style="color:#006000; text-decoration:underline">
              infoarena</a> cu numele <em><?= html_escape($user['full_name']) ?></em>,
          utilizator <em><?= html_escape($user['username']) ?></em>,
          adresă de email <em><?= html_escape($user['email']) ?></em>.</p>

        <p>Dacă nu mai doreşti să primeşti astfel de mesaje
          <a style="color:#006000; text-decoration:underline"
              href="<?= html_escape(url_absolute(url_unsubscribe($user['username'], user_unsubscribe_key($user)))) ?>">
          dezabonează-te acum</a>.</p>

        <?php } else if (!$user_is_anonymous) { ?>

        <p>Eşti înscris pe
          <a href="<?= html_escape(url_absolute(url_home())) ?>"
                style="color:#006000; text-decoration:underline">
            infoarena</a> cu numele <em><?= html_escape($user['full_name']) ?></em>,
          utilizator <em><?= html_escape($user['username']) ?></em>,
          adresă de email <em><?= html_escape($user['email']) ?></em>.</p>

        <p>Dacă nu mai doreşti să primeşti astfel de mesaje în viitor,
          <a style="color:#006000; text-decoration:underline"
              href="<?= url_absolute(url_account()) ?>">modifică opţiunile contului tău</a>.</p>

        <?php } else { ?>

        <p>infoarena nu trimite mesaje nesolicitate. Acest mesaj a fost trimis
            pe email membrilor infoarena abonaţi la newsletter.</p>

        <p>Dacă nu mai doreşti să primeşti astfel de mesaje în viitor,
          <a style="color:#006000; text-decoration:underline"
              href="<?= url_absolute(url_account()) ?>">modifică opţiunile contului tău</a>.</p>

        <?php } ?>

        </div>
      </td></tr>
    </table>

    </td></tr>
</table>
</body>
</html>
