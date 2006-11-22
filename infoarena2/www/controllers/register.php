<?php

function controller_register($suburl) {
    require_once("register_profile_common.php");

    // Initialize view parameters.
    // form data goes in data.
    // form errors go in errors.
    // data and errors use the same names.
    $view = array();
    $data = array();
    $errors = array();

    // page title
    $view['title'] = 'Inregistrare';
    $view['page_name'] = 'register';

    if ('save' == $suburl) {
        // user submitted registration form. Process it

        // 1. validate data
        $data['username'] = getattr($_POST, 'username');
        $data['password'] = getattr($_POST, 'password');
        $data['password2'] = getattr($_POST, 'password2');
        $data['full_name'] = getattr($_POST, 'full_name');
        $data['email'] = getattr($_POST, 'email');
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

        $errors = validate_data($data);

        // ==register specific validation==

        if (0 == strlen($data['username'])) {
            $errors['username'] = 'Nu ati introdus un nume utilizator';
        }

        if (0 == strlen($data['password'])) {
            $errors['password'] = 'Nu ati introdus o parola';
        }

        if ($data['email']) {
            if (!preg_match('/[^@]+@.+\..+/', $data['email'])) {
                $errors['email'] = 'Adresa de e-mail invalida';
            }
            elseif (user_get_by_email($data['email'])) {
                $errors['email'] = 'Email deja existent';
            }
        }

        // 2. process
        if (!$errors) {
            // create database entry for user
            $qdata = $data;
            unset($qdata['password2']); // don't want to add this to db
            $ia_user = user_create($qdata);

            if ($ia_user) {
                // also create SMF user
                require_once('smf.php');
                $smf_id = smf_create_user($ia_user);
                log_assert($smf_id, "SMF user for {$ia_user['username']} "
                                    ."was not created.");

                // redirect to login
                flash("Ai fost inregistrat. Acum te rugam sa te autentifici.");
                redirect(url("login"));
            }
        }
        else {
            flash_error('Am intalnit probleme, va rugam verificati datele cu rosu');
        }
    }
    else {
        // form is displayed for the first time. Fill in default values.
        $data['newsletter'] = 1;
        $data['email'] = '@';
        $data['country'] = 'Romania';
    }

    // attach form is displayed for the first time or a validation error occured
    $view['register'] = true;
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['topnav_select'] = 'register';
    execute_view_die('views/profile.php', $view);
}

?>
