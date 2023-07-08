<?php

require_once(Config::ROOT."common/db/user.php");
require_once(Config::ROOT."common/db/attachment.php");
require_once(Config::ROOT."common/avatar.php");
require_once(Config::ROOT."www/controllers/account_validator.php");
require_once(Config::ROOT."www/config.php");
require_once(Config::ROOT."common/avatar.php");

// Controller to update user profile
// $username is the name of the user to edit.
function controller_account($username = null) {
    global $identity_user;

    // This should fail for anon users.
    identity_require_login();

    // Get the user we have to edit.
    if ($username === null) {
        $user = $identity_user;
        $username = $identity_user['username'];
    } else {
        $user = user_get_by_username($username);
        if (!$user) {
            FlashMessage::addError('Cont de utilizator inexistent.');
            redirect(url_home());
        }
    }

    // Check security.
    identity_require('user-editprofile', $user);

    // editing own profile ?
    $ownprofile = (getattr($user, 'id') == getattr($identity_user, 'id'));

    // here we collect user input & validation errors
    $data = array();
    $errors = array();

    if (request_is_post()) {
        // user submitted profile form. Process it

        // get data and validate it
        $data['username'] = trim(request('username', $user['username']));
        $data['passwordold'] = request('passwordold');
        $data['password'] = request('password');
        $data['password2'] = request('password2');
        $data['full_name'] = trim(request('full_name', $user['full_name']));
        $data['email'] = trim(request('email', $user['email']));

        // Security level, only if allowed.
        $data['security_level'] = request('security_level', $user['security_level']);
        if ($user['security_level'] != $data['security_level']) {
            identity_require('user-change-security', $user);
        }

        $errors = validate_profile_data($data, $user);

        // validate tag data
        $data['tags'] = request('tags', tag_build_list("user", $user['id'], "tag"));
        tag_validate($data, $errors);

        // validate avatar
        // FIXME: This should leverage attachment creation code
        if (array_key_exists('avatar', $_FILES)) {
            $avatar = basename($_FILES['avatar']['name']);
            $mime_type = $_FILES['avatar']['type'];
            if ($avatar) {
                $avatar_size = $_FILES['avatar']['size'];
                // Check file size
                if ($avatar_size < 0 || $avatar_size > IA_AVATAR_MAXSIZE) {
                    $errors['avatar'] = 'Fișierul depășește limita de '
                                        .(IA_AVATAR_MAXSIZE / 1024).' KB';
                }
            }
        } else {
            $avatar = null;
        }

        // process data
        if (!$errors) {
            // upload avatar; similar to attachments
            // FIXME: use attachments code. Too bad attachments code is just as ugly.
            if ($avatar) {
                // Add the file to the database.
                $user_page = Config::USER_TEXTBLOCK_PREFIX.$user['username'];
                $file_name = 'avatar';
                $attach = attachment_get($file_name, $user_page);
                if ($attach) {
                    // Attachment already exists, overwrite.
                    attachment_update($attach['id'], $file_name, $avatar_size,
                                      $mime_type, $user_page, $user['id'],
                                      remote_ip_info(), false);
                } else {
                    // New attachment. Insert.
                    attachment_insert($file_name, $avatar_size,
                                      $mime_type, $user_page, $user['id'],
                                      remote_ip_info(), false);
                }

                // check if update/insert went ok
                $attach = attachment_get($file_name, $user_page);
                if (!$attach) {
                    $errors['avatar'] = 'Nu am putut salva avatarul în baza de date.';
                }

                // write the file on disk.
                if (!$errors) {
                    $disk_name = attachment_get_filepath($attach);
                    $errors['avatar'] = avatar_update(
                            $_FILES['avatar']['tmp_name'], $disk_name,
                            $user['username']);
                }
            }

            // Create new user entry.
            $new_user = $user;

            // modify user entry in database
            if (0 !== strlen($data['password'])) {
                $new_user['password'] = user_hash_password(
                        $data['password'], $new_user['username']);
            } else {
                $new_user['password'] = $user['password'];
            }
            $new_user['full_name'] = $data['full_name'];
            $new_user['email'] = $data['email'];
            $new_user['security_level'] = $data['security_level'];

            // update database entry
            user_update($new_user);
            $new_user = user_get_by_id($user['id']);

            // if changing own profile, reload identity information
            if ($ownprofile) {
                $identity_user = $new_user;
                identity_update_session($identity_user);
            }

            // update tags info
            if (identity_can('user-tag', $new_user)) {
                tag_update("user", $new_user['id'], "tag", $data['tags']);
            }
            $data['tags'] = tag_build_list("user", $new_user['id'], "tag");

            // done. redirect to same page so user has a strong confirmation
            // of data being saved
            FlashMessage::addSuccess("Am salvat modificările.");
        } else {
            FlashMessage::addError('Am întâlnit probleme. Verifică datele introduse.');
        }
    } else {
        // form is displayed for the first time. Fill in default values
        $data = $user;
        $data['tags'] = tag_build_list("user", $user['id'], "tag");

        // unset some fields we do not $data to carry
        unset($data['id']);
        unset($data['username']);
        unset($data['password']);
    }

    if (!identity_can('user-change-security', $user)) {
        unset($data['security_level']);
    }

    // attach form is displayed for the first time or validation error occurred
    // display form
    $view = array();

    // FIXME: belongs in view.
    if ($ownprofile) {
        $view['title'] = 'Contul meu';
    } else {
        $view['title'] = 'Modifică date pentru '.$user['username'];
    }
    $view['user'] = $user;
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['action'] = url_account($user['username']);
    $view['avatar_exists'] = attachment_get('avatar', Config::USER_TEXTBLOCK_PREFIX .
            $user['username']);
    execute_view_die('views/account.php', $view);
}
