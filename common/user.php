<?php

require_once(Config::ROOT."common/db/user.php");

// Password hash function.
//
// Also takes into account user name so that users
// sharing the same password can't be detected
function user_hash_password($password, $username) {
    return sha1(strtolower($username).$password);
}

// Computes reset password confirmation key.
// User must supply this in order to reset their password.
// $user is user object as returned by user_get_by_username(...)
function user_resetpass_key($user) {
    $key = sha1('r:'.$user['username'].':'.$user['password'].':'.Config::RESET_PASSWORD_SALT);

    // trim key. make it shorter since long URLs suck in text/plain emails
    return substr($key, 0, 16);
}

// Initialize an user struct
function user_init()
{
    $user = array();
    $user['security_level'] = 'normal';
    $user['rating_cache'] = 0;
    $user['id'] = -1;

    return $user;
}

// Validate an user struct.
function user_validate($user) {
    $errors = array();

    log_assert(is_array($user), "You didn't even pass an array.");

    // User id.
    if (!array_key_exists('id', $user)) {
        $errors['id'] = "Lipsește identificatorul de utilizator.";
    } else if (!is_user_id($user['id'])) {
        $errors['id'] = "Identficator de utilizator invalid.";
    }

    // Username
    if (!array_key_exists('username', $user)) {
        $errors['username'] = 'Lipsește numele de utilizator.';
    } else if (2 > strlen($user['username'])) {
        $errors['username'] = 'Numele de utilizator este prea scurt.';
    } else if (60 < strlen($user['username'])) {
        $errors['username'] = 'Numele de utilizator este prea lung.';
//    } else if (!is_user_name($user['username'])) {
//        $errors['username'] = 'Numele utilizator contine caractere invalide.';
    }

    // Password
    if (!array_key_exists('password', $user)) {
        $errors['password'] = 'Lipsește parola.';
    }

    // E-mail
    if (!array_key_exists('email', $user)) {
        $errors['email'] = 'Lipsește adresa de e-mail.';
//    } else if (!is_valid_email($user['email'])) {
//        $errors['email'] = 'Adresa de e-mail introdusa este invalida.';
    }

    // Full name
    if (!array_key_exists('full_name', $user)) {
        $errors['full_name'] = 'Lipsește numele complet.';
    } else if (strlen($user['full_name']) < 1) {
        $errors['full_name'] = 'Numele este prea scurt.';
    }

    // Security level
    if (!array_key_exists('security_level', $user)) {
        $errors['security_level'] = "Lipsește nivelul de securitate.";
    } else if ($user['security_level'] != 'normal' &&
               $user['security_level'] != 'helper' &&
               $user['security_level'] != 'admin' &&
               $user['security_level'] != 'intern') {
        $errors['security_level'] = "Nivel de securitate invalid.";
    }

    return $errors;
}

function user_is_admin($user) {
    if (!$user) {
        return false;
    }
    log_assert_valid(user_validate($user));
    return $user['security_level'] === 'admin';
}

?>
