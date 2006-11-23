<?php

// Profile controller.
function controller_profile($suburl) {
    require_once("register_profile_common.php");

    global $identity_user;
    // FIXME: Actually, we should allow changing ANY user profile as long
    // as we have the proper permissions.
    identity_require('user-editprofile', $identity_user);

    // Initialize view parameters.
    // form data goes in data.
    // form errors go in errors.
    // data and errors use the same names.
    $view = array();
    $data = array();
    $errors = array();

    // page title
    $view['title'] = 'Modificare profil';
    $view['page_name'] = "profile";

    // get user avatar and username
    $view['username'] = $identity_user['username'];

    if ($suburl == 'save') {
        // user submitted profile form. Process it

        // 1. validate data
        $data['password_old'] = getattr($_POST, 'password_old');
        $data['password'] = getattr($_POST, 'password');
        $data['password2'] = getattr($_POST, 'password2');
        $data['email'] = getattr($_POST, 'email');
        $data['full_name'] = getattr($_POST, 'full_name');
        $data['country'] = getattr($_POST, 'country');
        $data['county'] = getattr($_POST, 'county');
        $data['quote'] = getattr($_POST, 'quote');
        $data['birthday'] = getattr($_POST, 'birthday');
        $data['newsletter'] = (getattr($_POST, 'newsletter') == 'on' ?1:0);
        $data['city'] = getattr($_POST, 'city');
        $data['workplace'] = getattr($_POST, 'workplace');
        $data['study_level'] = getattr($_POST, 'study_level');
        $data['abs_year'] = getattr($_POST, 'abs_year');
        $data['postal_address'] = getattr($_POST, 'postal_address');
        $data['phone'] = getattr($_POST, 'phone');
        $data['lines_per_page'] = getattr($_POST, 'lines_per_page');

        $errors = validate_data($data);

        // ==profile specific validation==

        if (!preg_match('/[0-9]{1,2}/', $data['lines_per_page'])) {
            $errors['lines_per_page'] = 'Trebuie introdus un numar pana in 100';
            $errors['active_tab'] = 'profileData';
        }
        elseif (5 > $data['lines_per_page']) {
            $errors['lines_per_page'] = 'Minim 5 linii per pagina';
            $errors['active_tab'] = 'profileData';
        }

        // changing e-mail address or specifying new password forces user
        // to enter enter current password
        if ($identity_user['email'] != $data['email'] || $data['password']) {
            if (!$data['password_old']) {
                $errors['password_old'] = 'Va rugam sa introduceti parola '
                                          .'curenta pentru a schimba adresa '
                                          .'de e-mail.';
            }
        }

        // validate email
        if ($identity_user['email'] != $data['email']) {
            if (!preg_match('/[^@]+@.+\..+/', $data['email'])) {
                $errors['email'] = 'Adresa de e-mail invalida';
            }
            elseif (user_get_by_email($data['email'])) {
                $errors['email'] = 'Email deja existent';
            }
        }

        // validate current password
        if ($data['password_old']) {
            if (!user_test_password($identity_user['username'],
                                    $data['password_old'])) {
                $errors['password_old'] = 'Parola curenta nu este buna';
            }
        }

        // validate new password and confirmation field
        if ($data['password']) {
            if (4 >= strlen(trim($data['password']))) {
                $errors['password'] = 'Parola noua este prea scurta';
            }
            elseif ($data['password'] != $data['password2']) {
                $errors['password2'] = 'Parolele nu coincid';
            }
        }

        // -- avatar validation code --
        $avatar = basename($_FILES['avatar']['name']);
        $mime_type = $_FILES['avatar']['type'];
        if ($avatar) {
            $avatar_size = $_FILES['avatar']['size'];
            // Check file size
            if ($avatar_size < 0 || $avatar_size > IA_AVATAR_MAXSIZE) {
                $errors['avatar'] = 'Fisierul depaseste limita de ' . (IA_AVATAR_MAXSIZE / 1024).' KB';
            }
        }

        // 2. process
        if (!$errors) {
            $qdata = $data;
            unset($qdata['avatar']);
            // -- avatar upload --
            // similar to attachments
            if ($avatar) {
                // Add the file to the database.
                $user_page = 'user/' . $identity_user['username'];
                $file_name = 'avatar';
                $attach = attachment_get($file_name, $user_page);
                if ($attach) {
                    // Attachment already exists, overwrite.
                    attachment_update($attach['id'], $file_name, $avatar_size, $mime_type,
                                      $user_page, $identity_user['id']);
                }
                else {
                    // New attachment. Insert.
                    attachment_insert($file_name, $avatar_size,
                                      $mime_type, $user_page, $identity_user['id']);
                }

                // check if update/insert went ok
                $attach = attachment_get($file_name, $user_page);
                if (!$attach) {
                    $errors['avatar'] = 'Avatarul nu a putut fi salvat in baza de date.';
                }

                // Write the file on disk.
                if (!$errors) {
                    $qdata['avatar'] = $attach['id'];
                    // FIXME: There should be a unified name of computing file paths for attachments
                    // FIXME: See controllers/attachment.php
                    $disk_name = attachment_get_filepath($attach);
                    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $disk_name)) {
                        $errors['avatar'] = 'Fisierul nu a putut fi incarcat pe server.'; 
                    }
                }

                if ($errors) {
                    $view['active_tab'] = 'profileData';
                    flash_error("Eroare la uploadul avatarului");
                    redirect(url(""));
                }
            }

            // modify user in database
            if (0 == strlen($qdata['password'])) {
                unset($qdata['password']);
            }
            else {
                // when updating user password, it is mandatory to also specify
                // username
                $qdata['username'] = $identity_user['username'];
            }
            // trim unwanted/invalid fields
            unset($qdata['password2']);
            unset($qdata['password_old']);
            unset($qdata['avatar_size']);
            // update
            if (user_update($qdata, $identity_user['id'])) {
                $new_user = user_get_by_id($identity_user['id']);

                // propagate changes to SMF
                smf_update_user($new_user);

                // force reload of user info
                $identity_user = $new_user;
                identity_start_session($identity_user);

                // redirect to home
                flash("Modificarile de profil au fost efectuate cu succes.");
                redirect(url(""));
            }
        }
        else {
            flash_error('Am intalnit probleme, va rugam verificati datele cu rosu');
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

    // focus tab with errors
    if (isset($errors['active_tab'])) {
        $view['active_tab'] = $errors['active_tab'];
        unset($errors['active_tab']);
    }
    else {
        $view['active_tab'] = 'generalData';
    }

    // attach form is displayed for the first time or a validation error occured
    $view['register'] = false;
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['topnav_select'] = 'profile';
    execute_view_die('views/profile.php', $view);
}

?>
