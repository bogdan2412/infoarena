<?php

function controller_login() {
    global $identity_user;
    identity_require('user-login', $identity_user);

    // `data` dictionary is a dictionary with data to be displayed by form view
    // when displaying the form for the first time, this is filled with
    $data = array();

    // here we store validation errors.
    // It is a dictionary, indexed by field names
    $errors = array();

    // action
    $action = request('action');

    if ('login' == $action) {
        // Validate data here and place stuff in errors.
        $data['username'] = getattr($_POST, 'username');
        $data['password'] = getattr($_POST, 'password');
        $user = user_test_password($data['username'], $data['password']);
        if (!$user) {
            $user = user_test_ia1_password($data['username'], $data['password']);
            if (!$user) {
                $errors = true;
            }
            else {
                // update password to the SHA1 algorithm
                user_update(array('password' => $data['password']),
                            $user['id']);
            }
        }

        // process
        if (!$errors) {
            // persist user to session (login)
            identity_start_session($user);

            flash('Bine ati venit!');

            // redirect
            if (isset($_SESSION['_ia_redirect'])) {
                $url = $_SESSION['_ia_redirect'];
                unset($_SESSION['_ia_redirect']);

                redirect(IA_URL_HOST . $url);
            }
            else {
                redirect(url(''));
            }
        }
        else {
            flash_error('Numele de utilizator inexistent sau parola ' .
                        'incorecta. Va rugam sa incercati din nou.');
        }
    }

    // always reset password before displaying web form
    $data['password'] = '';

    $view['page_name'] = "login";
    $view['title'] = "Login";
    $view['form_values'] = $data;
    $view['form_errors'] = array();
    $view['action'] = url('login', array('action' => 'login'));

    execute_view_die('views/login.php', $view);
}

?>
