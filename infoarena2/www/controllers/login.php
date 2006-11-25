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
            //  - session lifetime may be 5d or until browser is closed
            $lifetime = ($data['remember'] ? 5*24*60*60 : 0);
            identity_start_session($user, $lifetime);

            flash('Bine ai venit!');

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
                        'incorecta. Incearca din nou.');
        }
    }

    // always reset password before displaying web form
    $data['password'] = '';

    $view['page_name'] = "login";
    $view['title'] = "Autentificare";
    $view['form_values'] = $data;
    $view['form_errors'] = array();
    $view['topnav_select'] = 'login';
    $view['no_sidebar_login'] = true;

    execute_view_die('views/login.php', $view);
}

?>
