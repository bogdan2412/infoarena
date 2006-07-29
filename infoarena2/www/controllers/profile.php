<?php

// Profile controller.
function controller_profile($suburl)
{
    identity_require('edit-profile');
    define('IA_AVATAR_MAXSIZE', 2000000);
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

    // get user avatar
    $qres = attachment_get_by_id($identity_user['avatar']);
    $view['avatar'] = $qres['name'];
    $view['username'] = $identity_user['username'];

    if ($suburl == 'save') {
        // user submitted profile form. Process it

        // 1. validate data
        $data['password_old'] = getattr($_POST, 'password_old');
        $data['password'] = getattr($_POST, 'password');
        $data['password2'] = getattr($_POST, 'password2');
        $data['email'] = getattr($_POST, 'email');
        
        if (0 != strlen($data['password']) ||
            $data['email'] != $identity_user['email']) {
            if (!user_test_password($identity_user['username'],
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
            if ($data['email'] != $identity_user['email']) {
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

        // -- avatar validation code --
        // TODO: Limit avatar dimensions
        $avatar = basename($_FILES['avatar']['name']);
        $avatar_size = $_FILES['avatar']['size'];
        // Validate filename. This limits attachment names, and it sucks
        if (!preg_match('/^[a-z0-9\.\-_]+$/i', $avatar)) {
            $errors['avatar'] = 'Nume de fisier invalid (nu folositi spatii)';
        }
        // Check file size
        if ($avatar_size < 0 ||
            $avatar_size > IA_AVATAR_MAXSIZE) {
            $errors['avatar_size'] = 'Fisierul depaseste limita de ' .
                (IA_AVATAR_MAXSIZE / 1024).' kbytes';
        }

        $data['quote'] = getattr($_POST, 'quote');
        if (255 < strlen($data['quote'])) {
            $errors['quote'] = 'Citatul este prea mare';
        }

        $data['birthday'] = getattr($_POST, 'birthday');
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
            $qdata = $data;
            unset($qdata['avatar']);
            // -- avatar upload --
            // similar to attachments
            if ($avatar)
            {
                // Add the file to the database.
                $user_page = 'user/' . $identity_user['username'];
                $attach = attachment_get($avatar, $user_page);
                if ($attach) {
                    // Attachment already exists, overwrite.
                    $disk_name = attachment_update($avatar,
                                                $avatar_size,
                                                $user_page,
                                                $identity_user['id']);
                }
                else {
                    // New attachment. Insert.
                    $disk_name = attachment_insert($avatar,
                                                $avatar_size,
                                                $user_page,
                                                $identity_user['id']);
                }
                // Check if something went wrong.
                if (!isset($disk_name)) {
                    $errors['avatar'] = 'Avatarulul nu a putut fi atasat';
                }

                // Write the file on disk.
                if (!$errors) {
                    $qdata['avatar'] = $disk_name; // $disk_name is the attach id
                    $disk_name = IA_ATTACH_DIR . $disk_name;
                    //print_r($_FILES['avatar']);
                    if (!move_uploaded_file($_FILES['avatar']['tmp_name'],
                                            $disk_name)) {
                        $errors['avatar'] = 'Fisierul nu a putut fi '.
                                                    'incarcat pe server'; 
                    }
                }
                
                if ($errors) {
                    flash_error("Eroare la uploadul avatarului");
                    redirect(url(""));
                }
            }
            
            // modify user in database
            if (0 == strlen($qdata['password'])) {
                unset($qdata['password']);
            }
            unset($qdata['password2']);
            unset($qdata['password_old']);
            unset($qdata['avatar_size']);
            if (user_update($qdata, $identity_user['id'])) {
                // force reload of user info
                $identity_user = user_get_by_id($identity_user['id']);
                identity_start_session($identity_user);
                // redirect to home
                flash("Modificarile de profil au fost efectuate cu succes.");
                redirect(url(""));
            }
        }
    }
    else {
        // form is displayed for the first time. Fill in default values
        foreach ($identity_user as $key => $val) {
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
