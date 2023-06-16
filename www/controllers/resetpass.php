<?php

require_once(IA_ROOT_DIR."common/db/smf.php");
require_once(IA_ROOT_DIR."common/db/user.php");
require_once(IA_ROOT_DIR."common/user.php");
require_once(IA_ROOT_DIR."common/email.php");

// displays form to identify user. On submit it sends e-mail with confirmation
// link.
function controller_resetpass() {
    // `data` dictionary is a dictionary with data to be displayed by form view
    $data = array();

    // here we store validation errors.
    // It is a dictionary, indexed by field names
    $errors = array();

    // submit?
    $submit = request_is_post();

    if ($submit) {
        // 1. validate
        // check username
        $data['username'] = getattr($_POST, 'username');
        $data['email'] = getattr($_POST, 'email');
        if ($data['username']) {
            $user = user_get_by_username($data['username']);
            if (!$user) {
                $errors['username'] = 'Nu există niciun utilizator cu acest '
                                      .'nume de cont.';
            }
        }
        elseif ($data['email']) {
            $user = user_get_by_email($data['email']);
            if (!$user) {
                $errors['email'] = 'Nu există niciun utilizator cu această adresă '
                                   .'de e-mail.';
            }
        }

        if (isset($user) && $user) {
            // user was found

            // confirmation code
            $cpass = user_resetpass_key($user);

            // confirmation link
            $clink = url_absolute(url_resetpass_confirm($user['username'], $cpass));

            // email user
            $to = $user['email'];
            $subject = 'Recuperează utilizatorul și parola';
            $message = sprintf("
Ai solicitat ca parola contului tău de pe %s sa fie resetată.

Nume de cont: %s
Adresa ta de e-mail: %s
Numele tău: %s

Pentru a confirma aceasta acțiune, trebuie să vizitezi acest link:

----
%s
----

Dacă nu ai facut o astfel de solicitare, ignoră acest mesaj, iar parola nu va fi resetată.

Echipa %s
%s
",
                               SITE_NAME,
                               $user['username'],
                               $user['email'],
                               $user['full_name'],
                               $clink,
                               SITE_NAME,
                               IA_URL

            );

            // send email
            send_email($to, $subject, $message);

            // notify user
            flash('Am trimis instructiuni pe e-mail.');
            redirect(url_login());
        }
        else {
            flash_error('Trebuie să completezi cel puțin unul din câmpuri.');
        }
    }
    else {
        // initial display of form
    }

    // page title
    $view = array();
    $view['title'] = 'Recuperare parolă';
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['no_sidebar_login'] = true;
    execute_view_die('views/resetpass.php', $view);
}

// checks confirmation code and resets password
function controller_resetpass_confirm($username) {
    $cpass = request('c');

    // validate username
    if ($username) {
        $user = user_get_by_username($username);
    }
    if (!$user) {
        flash_error('Numele de utilizator este invalid.');
        redirect(url_home());
    }

    // validate confirmation code
    if ($cpass != user_resetpass_key($user)) {
        flash_error('Codul de confirmare nu este corect.');
        redirect(url_home());
    }

    // reset password
    $new_password = sha1(mt_rand(1000000, 9999999).IA_SECRET);
    $new_password = substr($new_password, 0, 6);
    $user['password'] = user_hash_password($new_password, $user['username']);
    user_update($user);

    // send email with new password
    $to = $user['email'];
    $subject = 'Parola nouă';
    $message = sprintf("
Ai solicitat și ai confirmat ca parola ta să fie resetată.

Parola nouă: %s
Numele contului: %s

Te poți autentifica aici:
%s

Echipa %s
%s
",
                       $new_password,
                       $user['username'],
                       url_login(),
                       SITE_NAME,
                       IA_URL);

    // send e-mail
    send_email($to, $subject, $message);

    // notify user
    flash('Parola a fost resetată și trimisă pe e-mail. Verifică-ți ' .
          'e-mail-ul ca să afli noua parolă.');
    redirect(url_login());
}

?>
