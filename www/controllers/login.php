<?php

require_once(Config::ROOT . "common/db/user.php");

function controller_login() {
    // `data` dictionary is a dictionary with data to be displayed by form view
    // when displaying the form for the first time, this is filled with
    $data = array();

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

        // obtain referer
        $referer = getattr($_SERVER, 'HTTP_REFERER', '');
        if ($referer == url_login()) {
            // we don't care about the login page
            $referer = null;
        }

        // process
        if (!$errors) {
            // persist user to session (login)
            $remember_user = ($data['remember'] ? true : false);
            identity_start_session($user, $remember_user);

            FlashMessage::addSuccess('Bine ai venit!');

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

            FlashMessage::addError($errors);
        }
    }

    Smart::assign([
      'remember' => $data['remember'] ?? false,
      'showSidebarLogin' => false,
      'username' => $data['username'] ?? '',
    ]);
    Smart::display('auth/login.tpl');
}
