<?php

// Profile controller.
function controller_profile($suburl)
{
    identity_require('edit-profile');

    global $identity_user;

    // Initialize view parameters.
    // form data goes in data.
    // form errors go in errors.
    // data and errors use the same names.
    $view = array();
    $data = array();
    $errors = array();

    // page title
    $view['title'] = 'Modificare profil';

    // WTF is this?
    $current_user =& $identity_user;

    if ($suburl == 'save') {
        // user submitted profile form. Process it

        // 1. validate data
        $data['password_old'] = getattr($_POST, 'password_old');
        $data['password'] = getattr($_POST, 'password');
        $data['password2'] = getattr($_POST, 'password2');
        $data['email'] = getattr($_POST, 'email');
        
        if (0 != strlen($data['password']) ||
            $data['email'] != $current_user['email']) {
            if (!user_test_password($current_user['username'],
                                    $data['password_old'])) {
                $errors['password_old'] = 'Parola veche nu este buna';
            }
            
            if (4 >= strlen(trim($data['password']))) {
                $errors['password'] = 'Parola este prea scurta';
            }   
            else {
                if ($data['password'] != $data['password2']) {
                    $errors['password2'] = 'Parolele nu coincid';
                }
            }
            if ($data['email'] != $current_user['email']) {
                if (!preg_match('/[^@]+@.+\..+/', $data['email'])) {
                    $errors['email'] = 'Adresa de e-mail invalida';
                }
                else {
                    if (user_get_by_email($data['email'])) {
                        $errors['email'] = 'Email deja existent';
                    }
                }
            }

        }
        $data['full_name'] = getattr($_POST, 'full_name');
        if (3 >= strlen(trim($data['full_name']))) {
            $errors['full_name'] = 'Nu ati completat numele';
        }
        
        $data['country'] = getattr($_POST, 'country');
        if (!preg_match('/^[a-z]+[a-z_\-\ ]*$/i', $data['country'])) {
            $errors['country'] = 'Tara necunoscuta';
        }

        $data['county'] = getattr($_POST, 'county');
        if ($data['county'] &&
            !preg_match('/^[a-z]+[a-z_\-\ ]*$/i', $data['county'])) {
            $errors['county'] = 'Judet necunoscut';
        }

        $data['avatar'] = getattr($_POST, 'avatar');
        // TODO: validate avatar

        $data['quote'] = getattr($_POST, 'quote');
        if (255 < strlen($data['quote'])) {
            $errors['quote'] = 'Citatul este prea mare';
        }

        $data['birthday'] = getattr($_POST, 'birthday');
        if ($data['birthday']) {
            if (!ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $data['birthday'], $regs)) {
                $errors['birthday'] = 'Format data invalid';
            }
            elseif (!checkdate($regs[2], $regs[3], $regs[1])) {
                $errors['birthday'] = 'Data invalida';
            }
            elseif ($regs[1] > gmdate('Y') ||
                    ($regs[1] == gmdate('Y') && $regs[2] > gmdate('m'))) {
                $errors['birthday'] = 'Ziua de nastere este in viitor';
            }
        }

        $data['city'] = getattr($_POST, 'city');
        if ($data['city'] && !preg_match('/^[a-z]+[a-z_\-\ ]*$/i', $data['city'])) {
            $errors['city'] = 'Oras necunoscut';
        }

        $data['workplace'] = getattr($_POST, 'workplace');
        if ($data['workplace'] &&
            !preg_match('/^[a-z]+[a-z0-9_\-\.\ ]*$/i', $data['workplace'])) {
            $errors['workplace'] = 'Institut invalid';
        }

        $data['study_level'] = getattr($_POST, 'study_level');

        $data['abs_year'] = getattr($_POST, 'abs_year');
        if ($data['abs_year']) {
            if (!preg_match('/^[0-9]+$/', $data['abs_year'])) {
                $errors['abs_year'] = 'An de absolvire invalid';
            }
            if (!((int)$data['abs_year'] < 3000)) {
                $errors['abs_year'] = 'Anul de absolvire este introdus gresit';
            }
        }

        $data['postal_address'] = getattr($_POST, 'postal_address');

        $data['phone'] = getattr($_POST, 'phone');
        if ($data['phone']) {
            if (2 >= strlen(trim($data['phone']))) {
                $errors['phone'] = 'Numarul de telefon este prea mic..';
            }
            else {
                if (!preg_match('/^[0-9\-\+\ ]+$/', $data['phone'])) {
                    $errors['phone'] = 'Numarul de telefon este invalid';
                }
            }
        }

        // 2. process
        if (!$errors) {
            // modify user in database
            $qdata = $data;
            if (0 == strlen($qdata['password'])) {
                unset($qdata['password']);
            }
            unset($qdata['password2']);
            unset($qdata['password_old']);
            if (user_update($qdata, $current_user['id'])) {
                // redirect to home
                flash("Modificarile de profil au fost efectuate cu succes.");
                redirect(url(""));
            }
        }
    }
    else {
        // form is displayed for the first time. Fill in default values
        foreach ($current_user as $key => $val) {
            $data[$key] = $val;
        }
        if (0 == $data['birthday']) {
            unset($data['birthday']);
        }
        
        unset($data['id']);
        unset($data['username']);
        unset($data['password']);
    }

    // attach form is displayed for the first time or a validation error occured
    $view['register'] = false;
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    execute_view('views/profile.php', $view);
}
?>
