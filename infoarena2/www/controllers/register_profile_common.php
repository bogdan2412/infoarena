<?php

function validate_data($data)
{
    $errors = array();
    
    // isset to avoid notice
    if (isset($data['username']) && $data['username']) {
        if (3 >= strlen(trim($data['username']))) {
            $errors['username'] = 'Nume utilizator prea scurt';
        }
        elseif (!preg_match('/^[a-z]+[a-z0-9_\-\.]*$/i', $data['username'])) {
            $errors['username'] = 'Nume utilizator invalid';
        }
        elseif (user_get_by_username($data['username'])) {
            $errors['username'] = 'Nume utilizator deja existent';
        }
    }

    if ($data['password'] || $data['password2']) {
        if (4 >= strlen(trim($data['password']))) {
            $errors['password'] = 'Parola trebuie sa aibe minim 5 caractere';
        }
        elseif ($data['password'] != $data['password2']) {
            $errors['password2'] = 'Parolele nu coincid';
        }
    }

    if (3 >= strlen(trim($data['full_name']))) {
        $errors['full_name'] = 'Nu ati completat numele';
    }

    if (!$data['country']) {
        $errors['country'] = 'Va rugam completati tara';
    }
    elseif (!preg_match('/^[a-z]+[a-z_\-\ ]*$/i', $data['country'])) {
        $errors['country'] = 'Tara necunoscuta';
    }

    if ($data['county']) {
        if (!preg_match('/^[a-z]+[a-z_\-\ ]*$/i', $data['county'])) {
            $errors['county'] = 'Judet necunoscut';
        }
    }

    if ($data['quote'])
    {
        if (255 < strlen($data['quote'])) {
            $errors['quote'] = 'Citatul este prea mare';
        }
    }
    
    if ($data['birthday']) {
        if (!ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",
                  $data['birthday'], $regs)) {
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

    if ($data['city']) {
        if (!preg_match('/^[a-z]+[a-z_\-\ ]*$/i', $data['city'])) {
            $errors['city'] = 'Oras necunoscut';
        }
    }

    if ($data['workplace']) {
        if (!preg_match('/^[a-z]+[a-z0-9_\-\.\ ]*$/i', $data['workplace'])) {
            $errors['workplace'] = 'Institut invalid';
        }
    }

    if ($data['abs_year']) {
        if (!preg_match('/^[0-9]+$/', $data['abs_year'])) {
            $errors['abs_year'] = 'An de absolvire invalid';
        }
        elseif (!((int)$data['abs_year'] < 3000)) {
            $errors['abs_year'] = 'Anul de absolvire este introdus gresit';
        }
    }

    if ($data['phone']) {
        if (2 >= strlen(trim($data['phone']))) {
            $errors['phone'] = 'Numarul de telefon este prea mic';
        }
        elseif (!preg_match('/^[0-9\-\+\ ]+$/', $data['phone'])) {
            $errors['phone'] = 'Numarul de telefon este invalid';
        }
    }

    return $errors;
}
?>
