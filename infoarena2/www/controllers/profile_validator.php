<?php

// validates registration input data (wrapper for validate_data)
function validate_register_data($data) {
    return validate_user_data($data, true);
}

// validates user profile input data (wrapper for validate_data)
function validate_profile_data($data) {
    return validate_user_data($data, false);
}

// validate fields found in register/profile forms
function validate_user_data($data, $register) {
    $errors = array();

    // username
    if ($register) {
        if (!$data['username']) {
            $errors['username'] = 'Nu ati specificat numele de utilizator.';
        }
        elseif (4 > strlen(trim($data['username']))) {
            $errors['username'] = 'Nume de utilizator este prea scurt';
        }
        elseif (!preg_match('/^[a-z]+[a-z0-9_\-\.]*$/i', $data['username'])) {
            $errors['username'] = 'Numele utilizator contine caractere '
                                  .'invalide.';
        }
        elseif (user_get_by_username($data['username'])) {
            $errors['username'] = 'Nume utilizator rezervat de altcineva. Va '
                                  .'rugam alegeti altul.';
        }
    }

    // email
    if (!$data['email']) {
        $errors['email'] = 'Nu ati introdus adresa de e-mail.';
    }
    elseif (!preg_match('/[^@]+@.+\..+/', $data['email'])) {
        $errors['email'] = 'Adresa de e-mail introdusa este invalida.';
    }
    elseif (user_get_by_email($data['email'])) {
        $errors['email'] = 'Adresa de e-mail este deja asociata unui cont. '
                           .'Reseteaza-ti parola daca ai uitat-o.';
    }

    // password
    if ($register || $data['password'] || $data['password2']) {
        if (!$data['password']) {
            $errors['password'] = 'Nu ati introdus parola.';
        }
        elseif (4 > strlen(trim($data['password']))) {
            $errors['password'] = 'Parola introdusa este prea scurta.';
        }
        elseif ($data['password'] != $data['password2']) {
            $errors['password2'] = 'Parolele nu coincid.';
        }
    }

    // full name
    if (6 > strlen(trim($data['full_name']))) {
        $errors['full_name'] = 'Nu ati completat numele.';
    }

    // terms & conditions
    if ($register && !$data['tnc']) {
        $errors['tnc'] = 'Ca sa te inregistrezi trebuie sa fii de acord cu '
                         .'aceste conditii.';
    }

    return $errors;
}

?>
