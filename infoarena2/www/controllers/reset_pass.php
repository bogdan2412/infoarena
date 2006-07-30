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
            $new_password = md5($user['password']);
            user_update(array('password' => $new_password), $user['id']);
            
            // email user
            // TODO FIXME: check if new-line is \r\n or \n
            $to = $user['email'];
            $subject = 'Recupereaza nume utilizator si parola de pe infoarena';
            $message = 'Buna ziua!' . '\r\n' .
                       'Parola contului dumneavoastra de pe infoarena.ro a ' .
                            'fost resetata.' . '\n\r' . 
                       'nume utilizator:\t' . $user['username'] . '\r\n' .
                       'parola noua:\t' . $new_password . '\r\n';
            // TODO FXME: we could add more content in mail!
            send_email($to, $subject, $message);

            // notify user
            flash('Am trimis email cu numele de utilizator si parola noua. ' .
                  'Va rugam sa va autentificati');
            redirect(url('login'));
        }
    }

    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    execute_view('views/reset_pass.php', $view);
}
?>