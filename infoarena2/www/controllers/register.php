<?php

// Initialize view parameters.
$view = array();

// page title
$view['title'] = 'Inregistrare';

// data` dictionary is a dictionary with data to be displayed by form view
// when displaying the form for the first time, this is filled with
$data = array();
$view['data'] =& $data;

// here we store validation errors. It is a dictionary, indexed by field names
$errors = array();
$view['errors'] =& $errors;

if ('save' == getattr($urlpath, 1, null)) {
    // user submitted registration form. Process it

    // 1. validate data
    $data['username'] = getattr($_POST, 'username');
    if (3 >= strlen(trim($data['username'])) ||
        !preg_match('/^[a-z]+[a-z0-9_\-\.]*$/i', $data['username'])) {
        $errors['username'] = 'Nume utilizator invalid';
    }
    else {
        if (user_get_by_username($data['username'])) {
            $errors['username'] = 'Nume utilizator deja existent';
        }
    }
    
    $data['password'] = getattr($_POST, 'password');
    $data['password2'] = getattr($_POST, 'password2');
    if (4 >= strlen(trim($data['password']))) {
        $errors['password'] = 'Parola este prea scurta';
    }
    else {
        if ($data['password'] != $data['password2']) {
            $errors['password2'] = 'Parolele nu coincid';
        }
    }
    
    $data['full_name'] = getattr($_POST, 'full_name');
    if (3 >= strlen(trim($data['full_name']))) {
        $errors['full_name'] = 'Nu ati completat numele';
    }
    
    $data['email'] = getattr($_POST, 'email');
    if (!preg_match('/[^@]+@.+\..+/', $data['email'])) {
        $errors['email'] = 'Adresa de e-mail invalida';
    }
    else {
        if (user_get_by_email($data['email'])) {
            $errors['email'] = 'Email deja existent';
        }
    }
    
    $data['country'] = getattr($_POST, 'country');
    if (!preg_match('/^[a-z]+[a-z_\-]*$/i', $data['country'])) {
        $errors['country'] = 'Tara necunoscuta';
    }

    $data['county'] = getattr($_POST, 'county');
    if ($data['county'] &&
        !preg_match('/^[a-z]+[a-z_\-]*$/i', $data['county'])) {
        $errors['county'] = 'Judet necunoscut';
    }

    $data['quote'] = getattr($_POST, 'quote');
    if (255 < strlen($data['quote'])) {
        $errors['quote'] = 'Citatul este prea mare';
    }

    $data['birthday'] = getattr($_POST, 'birthday');
    if ($data['birthday']) {
        if (ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})",
            $data['birthday'], $regs)) {
            if (!checkdate($regs[2], $regs[3], $regs[1])) {
                $errors['birthday'] = 'Data invalida';
            }
        }
        else {
            $errors['birthday'] = 'Format data invalid';
        }
    }

    $data['city'] = getattr($_POST, 'city');
    if ($data['city'] && !preg_match('/^[a-z]+[a-z_\-]*$/i', $data['city'])) {
        $errors['city'] = 'Oras necunoscut';
    }

    $data['workplace'] = getattr($_POST, 'workplace');
    if ($data['workplace'] &&
        !preg_match('/^[a-z]+[a-z0-9_\-\.]*$/i', $data['workplace'])) {
        $errors['workplace'] = 'Institut invalid';
    }
    // TODO: maybe more checks for workplace

    $data['study_level'] = getattr($_POST, 'study_level');

    $data['abs_year'] = getattr($_POST, 'abs_year');
    if ($data['abs_year']) {
        if (!preg_match('/^[0-9]+$/', $data['abs_year'])) {
            $errors['abs_year'] = 'An de absolvire invalid';
        }
        if (!(2000 < (int)$data['abs_year'] && (int)$data['abs_year'] < 3000)) {
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
            if (!preg_match('/^[0-9\-\+]+$/', $data['phone'])) {
                $errors['phone'] = 'Numarul de telefon este invalid';
            }
        }
    }

    // 2. process
    if (!$errors) {
        // add user to database
        $qdata = $data;
        unset($qdata['password2']);
        if (user_create($qdata)) {
            // redirect to login
            flash("Ai fost inregistrat. Acum te rugam sa te autentifici.");
            redirect(url("login"));
        }
    }
}
else {
    // form is displayed for the first time. Fill in default values.
    $data['email'] = '@';
    $data['country'] = 'Romania';
}

// attach form is displayed for the first time or a validation error occured
$view['register'] = true;
execute_view('views/profile.php', $view);
?>