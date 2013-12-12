<?php

require_once(IA_ROOT_DIR . 'common/tags.php');
require_once(IA_ROOT_DIR . 'common/db/tokens.php');

// validates registration input data (wrapper for validate_data)
function validate_register_data($data) {
    $errors = validate_user_data($data, true, null);

    // Give enough tokens back for a login
    $errors['captcha'] = check_captcha_for_tokens(IA_TOKENS_CAPTCHA, true);
    if (!$errors['captcha']) {
        unset($errors['captcha']);
    }

    return $errors;
}

// validates user profile input data (wrapper for validate_data)
function validate_profile_data($data, $user) {
    return validate_user_data($data, false, $user);
}

// validate fields found in register/profile forms
function validate_user_data($data, $register, $user = null) {
    $errors = array();

    log_assert($register ^ $user);

    // username
    if ($register) {
        if (!$data['username']) {
            $errors['username'] = 'Nu ati specificat numele de utilizator.';
        }
        elseif (4 > strlen(trim($data['username']))) {
            $errors['username'] = 'Nume de utilizator este prea scurt.';
        }
        elseif (60 < strlen(trim($data['username']))) {
            $errors['username'] = 'Nume de utilizator este prea lung.';
        }
        elseif (!is_user_name($data['username'])) {
            $errors['username'] = 'Numele utilizator contine caractere '
                                  .'invalide.';
        }
        elseif (user_get_by_username($data['username']) ||
                smf_get_member_by_name($data['username'])) {
            $errors['username'] = 'Nume utilizator rezervat de altcineva. Va '
                                  .'rugam alegeti altul.';
        }
    }

    // email
    if (!$data['email']) {
        $errors['email'] = 'Nu ati introdus adresa de e-mail.';
    }
    elseif (!is_valid_email($data['email'])) {
        $errors['email'] = 'Adresa de e-mail introdusa este invalida.';
    }

    // changing e-mail address or specifying new password forces user
    // to enter enter current password
    if (!$register && ($user['email'] != $data['email'])) {
        if (!$data['passwordold']) {
            $errors['passwordold'] = 'Introdu parola curenta (veche) pentru a '
                                      .'schimba adresa de e-mail.';
        }
    }

    // changing password forces user to enter current password
    if (!$register && ($data['password'] || $data['password2'])) {
        if (!$data['passwordold']) {
            $errors['passwordold'] = 'Introdu parola curenta (veche) pentru a '
                                     .'o schimba.';
        }
    }

    // When registering or changing e-mail address, make sure e-mail is unique
    if ($register || ($user['email'] != $data['email'])) {
        if (user_get_by_email($data['email'])) {
            $errors['email'] = 'Adresa de e-mail este deja asociata unui cont!'
                               .' Reseteaza-ti parola daca ai uitat-o.';
        }
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

    // current password
    if (!$register && $data['passwordold']) {
        if (!user_test_password($user['username'], $data['passwordold'])) {
            $errors['passwordold'] = 'Nu aceasta este parola curenta!';
        }
    }

    // full name
    if (6 > strlen(trim($data['full_name']))) {
        $errors['full_name'] = 'Nu ati completat numele.';
    }
    elseif (!is_user_full_name($data['full_name'])) {
        $errors['full_name'] = 'Numele contine caractere invalide.';
    }

    // terms & conditions
    if ($register && !$data['tnc']) {
        $errors['tnc'] = 'Ca sa te inregistrezi trebuie sa fii de acord cu '
                         .'aceste conditii.';
    }

    // Security
    if (!$register && array_key_exists('security_level', $data)) {
        if ($data['security_level'] != 'normal' &&
            $data['security_level'] != 'helper' &&
            $data['security_level'] != 'admin' &&
            $data['security_level'] != 'intern') {
            $errors['security_level'] = "Nivel de securitate invalid";
        }
    }

    return $errors;
}
