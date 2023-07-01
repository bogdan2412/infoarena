<?php

require_once(IA_ROOT_DIR . "common/db/user.php");

function controller_login() {
    // `data` dictionary is a dictionary with data to be displayed by form view
    // when displaying the form for the first time, this is filled with
    $data = array();

    // array for the captcha error
    $form_errors = array();

    // The flash error
    $errors = '';

    // process input?
    $submit = request_is_post();

    if ($submit) {
        // Validate data here and place stuff in errors.
        $data['username'] = getattr($_POST, 'username');
        $data['password'] = getattr($_POST, 'password');
        $data['remember'] = getattr($_POST, 'remember');
        $user = user_test_password($data['username'], $data['password']);
        if (!$user) {
            $user = user_test_ia1_password($data['username'], $data['password']);
            if (!$user) {
                $errors = 'Numele de utilizator inexistent sau parola ' .
                          'incorectă. Încearcă din nou.';
            }
            else {
                // update password to the SHA1 algorithm
                user_update(array('password' => $data['password'],
                                  'username' => $data['username']),
                            $user['id']);
            }
        }

        // Get some tokens from the captcha
        // It's not like that matters in any way because we do not receive
        // enough for the login cost
        $form_errors['captcha'] = check_captcha_for_tokens();

        // obtain referer
        $referer = getattr($_SERVER, 'HTTP_REFERER', '');
        if ($referer == url_login()) {
            // we don't care about the login page
            $referer = null;
        }

        // pay tokens for loging in
        if (!pay_tokens(IA_TOKENS_LOGIN)) {
            if ($form_errors['captcha']) {
                $errors = 'Vă rugăm să confirmați că sunteți om.';
                unset($form_errors['captcha']);
            }
        }

        // process
        if (!$errors) {
            // good user receives some tokens back enough to logout and login as a different user
            pay_tokens(-IA_TOKENS_LOGIN);
            // persist user to session (login)
            $remember_user = ($data['remember'] ? true : false);
            identity_start_session($user, $remember_user);

            flash('Bine ai venit!');

            // redirect
            if (isset($_SESSION['_ia_redirect'])) {
                // redirect to where identity_require() failed
                $url = $_SESSION['_ia_redirect'];
                unset($_SESSION['_ia_redirect']);
                redirect($url);
            }
            elseif ($referer) {
                // redirect to HTTP referer if set, but not to login
                redirect($_SERVER['HTTP_REFERER']);
            }
            else {
                // home, sweet home
                redirect(url_home());
            }
        }
        else {
            // save referer so we know where to redirect when login finally
            // succeeds.
            if (!isset($_SESSION['_ia_redirect']) && $referer) {
                $_SESSION['_ia_redirect'] = $_SERVER['HTTP_REFERER'];
            }

            flash_error($errors);
        }
    }

    // always reset password before displaying web form
    $data['password'] = '';

    $view = array();
    $view['page_name'] = "login";
    $view['title'] = "Autentificare";
    $view['form_values'] = $data;
    $view['form_errors'] = $form_errors;
    $view['no_sidebar_login'] = true;

    if (get_tokens() < IA_TOKENS_LOGIN) {
        $view['captcha'] = recaptcha_get_html(IA_CAPTCHA_PUBLIC_KEY, null,
                true);
    }

    execute_view_die('views/login.php', $view);
}
