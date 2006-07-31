<?php
function controller_reset_pass($suburl) {
    // `data` dictionary is a dictionary with data to be displayed by form view
    $data = array();

    // here we store validation errors.
    // It is a dictionary, indexed by field names
    $errors = array();

    $view = array();

    // page title
    $view['title'] = 'Recuperare parola';
    $view['page_name'] = "Recuperare parola";

    if ($suburl == 'reset') {
        // 1. validate
        // check username
        $data['username'] = getattr($_POST, 'username');
        $data['email'] = getattr($_POST, 'email');
        if ($data['username']) {
            $user = user_get_by_username($data['username']);
            if (!$user) {
                flash_error('Nu exista utilizator cu numele de cont ' .
                            $data['username'] . ' inregistrat pe site');
            }
        }
        elseif ($data['email']) {
            $user = user_get_by_email($data['email']);
            if (!$user) {
                flash_error('Nu exista utilizator cu emailul ' .
                            $data['email'] . ' inregistrat pe site');
            }
        }

        if (isset($user) && $user) { // user found
            // reset password
            $cpass = md5($user['password']);
            
            // email user
            /// TODO FIXME: check if new-line is '\r\n' or '\n'
            $to = $user['email'];
            $subject = 'Recupereaza nume utilizator si parola de pe infoarena';
            $message = 'Buna ziua!' . '\r\n' .
                       'Daca doriti ca parola contului dumneavoastra de pe ' .
                            'info-arena sa se reseteze, dati click pe linkul ' .
                            'din acest mail\n\r' .
                       'Numele contului: ' . $user['username'] . '\r\n' . 
                       'Link: ' . url('reset_pass/doit',
                                      array('username' => $user['username'],
                                            'cpass' => $cpass),
                                      true);
            /// TODO FXME: we could add more content in mail!
            send_email($to, $subject, $message);

            // notify user
            flash('Am trimis email cu numele de utilizator ' .
                  'si linkul de resetare. ' .
                  'Va rugam sa va verificati emailul');
            redirect(url('home'));
        }
    }
    else if ($suburl == 'doit') {
        $username = getattr($_GET, 'username');
        $cpass = getattr($_GET, 'cpass');
        if ($username) {
            $user = user_get_by_username($username);
            if ($user && md5($user['password']) == $cpass) {
                // all seems to be ok, reset pass and email user
                $new_password = mt_rand(1000000, 9999999);
                user_update(array('password' => $new_password), $user['id']);

                // send email with new password
                $to = $user['email'];
                $subject = 'Parola noua';
                $message = 'Numele contului: ' . $username . '\r\n' .
                           'Parola noua: ' . $new_password;
                send_email($to, $subject, $message);

                // notify yser
                flash('V-am trimis email cu noua parola. ' .
                      'Va rugam sa va autentificati');
                redirect(url('login'));
            }
        }

        // daca am ajuns aici, inseamna ca am intampinat o problema
        /// TODO FIXME: we could make a more rigurous error checking
        flash_error('Am intampinat probleme in procesul resetare a parolei!');
        redirect(url('home'));
    }

    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    execute_view('views/reset_pass.php', $view);
}
?>