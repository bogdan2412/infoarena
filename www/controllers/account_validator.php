<?php

require_once(IA_ROOT_DIR . 'common/tags.php');
require_once(IA_ROOT_DIR . 'common/db/tokens.php');

// validates registration input data (wrapper for validate_data)
function validate_register_data($data) {
    $errors = validate_user_data($data, true, null);

    // If we're going to sell our soul to evil technologies, at least make
    // sure we have to.
    if (IA_TOKENS_REGISTER) {
        // Give enough tokens back for a login
        $errors['captcha'] = check_captcha_for_tokens(IA_TOKENS_CAPTCHA, true);
        if (!$errors['captcha']) {
            unset($errors['captcha']);
        }
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

    log_assert($register ^ !empty($user));

    // username
    if ($register) {
        if (!$data['username']) {
            $errors['username'] = 'Nu ați specificat numele de utilizator.';
        }
        elseif (4 > strlen(trim($data['username']))) {
            $errors['username'] = 'Numele de utilizator este prea scurt.';
        }
        elseif (40 < strlen(trim($data['username']))) {
            $errors['username'] = 'Numele de utilizator este prea lung.';
        }
        elseif (!is_user_name($data['username'])) {
            $errors['username'] = 'Numele de utilizator conține caractere '
                                  .'invalide.';
        }
        elseif (user_get_by_username($data['username']) ||
                smf_get_member_by_name($data['username'])) {
            $errors['username'] = 'Numele de utilizator este rezervat de altcineva. Vă '
                                  .'rugăm alegeți altul.';
        }
    }

    // email
    if (!$data['email']) {
        $errors['email'] = 'Nu ați introdus adresa de e-mail.';
    }
    elseif (!is_valid_email($data['email'])) {
        $errors['email'] = 'Adresa de e-mail introdusă este invalidă.';
    }

    // changing e-mail address or specifying new password forces user
    // to enter enter current password
    if (!$register && ($user['email'] != $data['email'])) {
        if (!$data['passwordold']) {
            $errors['passwordold'] = 'Introdu parola curentă (veche) pentru a '
                                      .'schimba adresa de e-mail.';
        }
    }

    // changing password forces user to enter current password
    if (!$register && ($data['password'] || $data['password2'])) {
        if (!$data['passwordold']) {
            $errors['passwordold'] = 'Introdu parola curentă (veche) pentru a '
                                     .'o schimba.';
        }
    }

    // When registering or changing e-mail address, make sure e-mail is unique
    if ($register || ($user['email'] != $data['email'])) {
        if (user_get_by_email($data['email'])) {
            $errors['email'] = 'Adresa de e-mail este deja asociată unui cont.'
                               .' Resetează-ți parola dacă ai uitat-o.';
        }
    }

    // password
    if ($register || $data['password'] || $data['password2']) {
        if (!$data['password']) {
            $errors['password'] = 'Nu ați introdus parola.';
        }
        elseif (4 > strlen(trim($data['password']))) {
            $errors['password'] = 'Parola introdusă este prea scurtă.';
        }
        elseif ($data['password'] != $data['password2']) {
            $errors['password2'] = 'Parolele nu coincid.';
        }
    }

    // current password
    if (!$register && $data['passwordold']) {
        if (!user_test_password($user['username'], $data['passwordold'])) {
            $errors['passwordold'] = 'Nu aceasta este parola curentă.';
        }
    }

    // full name
    if (!is_user_full_name($data['full_name'])) {
        $errors['full_name'] =
            'Numele trebuie să înceapă cu o literă sau cifră și să conțină ' .
            '5-40 de litere, cifre, spații și semne de punctuație dintre -_.';
    }

    // terms & conditions
    if ($register && !$data['tnc']) {
        $errors['tnc'] = 'Ca să te înregistrezi trebuie să fii de acord cu '
                         .'aceste condiții.';
    }

    // Security
    if (!$register && array_key_exists('security_level', $data)) {
        if ($data['security_level'] != 'normal' &&
            $data['security_level'] != 'helper' &&
            $data['security_level'] != 'admin' &&
            $data['security_level'] != 'intern') {
            $errors['security_level'] = "Nivel de securitate invalid.";
        }
    }

    return $errors;
}
