<?php

require_once(IA_ROOT."common/db/user.php");
require_once(IA_ROOT."common/db/attachment.php");
require_once(IA_ROOT."common/db/smf.php");
require_once(IA_ROOT."www/controllers/account_validator.php");

// identify target user and check permission to edit profile
// Yields flash_error & redirect when username invalid or security error
function account_validate_user() {
    $username = request('username');
    if (!$username) {
        // no username specified. we're probably talking about
        // current remote user
        global $identity_user;
        $user = $identity_user;
    }
    else {
        // validate username
        $user = user_get_by_username($username);
    }

    // validate user
    if (!$user) {
        flash_error('Cont de utilizator inexistent');
        redirect(url(''));
    }

    // permission check
    identity_require('user-editprofile', $user);

    return $user;
}

// Controller to update user profile
function controller_account() {
    global $identity_user;
    $user = account_validate_user();

    // here we collect user input & validation errors
    $data = array();
    $errors = array();

    // submit?
    $submit = request_is_post();

    // editing own profile ?
    $ownprofile = (getattr($user, 'id') == getattr($identity_user, 'id'));

    if ($submit) {
        // user submitted profile form. Process it

        // validate data
        $data['username'] = getattr($_POST, 'username');
        $data['passwordold'] = getattr($_POST, 'passwordold');
        $data['password'] = getattr($_POST, 'password');
        $data['password2'] = getattr($_POST, 'password2');
        $data['full_name'] = getattr($_POST, 'full_name');
        $data['email'] = getattr($_POST, 'email');
        $data['newsletter'] = (getattr($_POST, 'newsletter') ? 1 : 0);
        $errors = validate_profile_data($data, $user);

        // validate avatar
        // FIXME: This should leverage attachment creation code
        $avatar = basename($_FILES['avatar']['name']);
        $mime_type = $_FILES['avatar']['type'];
        if ($avatar) {
            $avatar_size = $_FILES['avatar']['size'];
            // Check file size
            if ($avatar_size < 0 || $avatar_size > IA_AVATAR_MAXSIZE) {
                $errors['avatar'] = 'Fisierul depaseste limita de '
                                    .(IA_AVATAR_MAXSIZE / 1024).' KB';
            }
        }

        // process data
        if (!$errors) {
            $fields = $data;
            unset($fields['username']);

            // upload avatar; similar to attachments
            // FIXME: Leverage code for attachment creation
            if ($avatar) {
                // Add the file to the database.
                $user_page = TB_USER_PREFIX.$user['username'];
                $file_name = 'avatar';
                $attach = attachment_get($file_name, $user_page);
                if ($attach) {
                    // Attachment already exists, overwrite.
                    attachment_update($attach['id'], $file_name, $avatar_size,
                                      $mime_type, $user_page, $user['id']);
                }
                else {
                    // New attachment. Insert.
                    attachment_insert($file_name, $avatar_size,
                                      $mime_type, $user_page, $user['id']);
                }

                // check if update/insert went ok
                $attach = attachment_get($file_name, $user_page);
                if (!$attach) {
                    $errors['avatar'] = 'Avatar-ul nu a putut fi salvat in '
                                        .'baza de date.';
                }

                // write the file on disk.
                if (!$errors) {
                    $disk_name = attachment_get_filepath($attach);
                    if (!move_uploaded_file($_FILES['avatar']['tmp_name'],
                                            $disk_name)) {
                        $errors['avatar'] = 'Fisierul nu a putut fi incarcat '
                                            .'pe server.';
                    }
                }
            }

            // modify user entry in database
            if (0 == strlen(trim($fields['password']))) {
                unset($fields['password']);
            }
            else {
                // when updating user password, it is mandatory to also specify
                // username
                $fields['username'] = $user['username'];
            }
            // trim unwanted/invalid fields
            unset($fields['password2']);
            unset($fields['passwordold']);

            // update database entry
            user_update($fields, $user['id']);
            $new_user = user_get_by_id($user['id']);

            // propagate changes to SMF
            smf_update_user($new_user);

            // if changing own profile, reload identity information
            if ($ownprofile) {
                $identity_user = $new_user;
                identity_start_session($identity_user);
            }

            // done. redirect to same page so user has a strong confirmation
            // of data being saved
            flash("Modificarile au fost salvate! ".getattr($errors, 'avatar'));
            redirect(url('account', array('username' => $user['username'])));
        }
        else {
            flash_error('Am intalnit probleme. Verifica datele introduse.');
        }
    }
    else {
        // form is displayed for the first time. Fill in default values
        $data = $user;

        // unset some fields we do not $data to carry
        unset($data['id']);
        unset($data['username']);
        unset($data['password']);
    }

    // attach form is displayed for the first time or validation error occurred
    // display form
    $view = array();
    if ($ownprofile) {
        $view['title'] = 'Contul meu';
    }
    else {
        $view['title'] = 'Modifica date pentru '.$user['username'];
    }
    $view['user'] = $user;
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['action'] = url('account', array('username' => $user['username']));
    if ($ownprofile) {
        $view['topnav_select'] = 'profile';
    }
    execute_view_die('views/account.php', $view);
}

?>
