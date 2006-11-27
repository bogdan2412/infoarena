<?php

require_once(IA_ROOT."common/db/user.php");
require_once(IA_ROOT."www/controllers/account_validator.php");

function controller_register() {
    $submit = request_is_post();

    // Initialize view parameters.
    // form data goes in data.
    // form errors go in errors.
    // data and errors use the same names.
    $view = array();
    $data = array();
    $errors = array();

    if ($submit) {
        // user submitted registration form. Process it

        // 1. validate data
        $data['username'] = getattr($_POST, 'username');
        $data['password'] = getattr($_POST, 'password');
        $data['password2'] = getattr($_POST, 'password2');
        $data['full_name'] = getattr($_POST, 'full_name');
        $data['email'] = getattr($_POST, 'email');
        $data['newsletter'] = (getattr($_POST, 'newsletter') ? 1 : 0);
        $data['tnc'] = (getattr($_POST, 'tnc') ? 1 : 0);

        $errors = validate_register_data($data);

        // 2. process
        if (!$errors) {
            // create database entry for user
            $qdata = $data;
            unset($qdata['password2']); // don't want to add this to db
            unset($qdata['tnc']); // don't want to add this to db
            $ia_user = user_create($qdata);

            if ($ia_user) {
                // also create SMF user
                $smf_id = smf_create_user($ia_user);
                log_assert($smf_id, "SMF user for {$ia_user['username']} "
                                    ."was not created.");
                // redirect to login
                flash("Felicitari! Contul a fost creat. Acum te poti "
                      ."autentifica.");
                redirect(url("login"));
            }
        }
        else {
            flash_error('Am intalnit probleme. Verifica datele introduse.');
        }
    }
    else {
        // form is displayed for the first time. Fill in default values.
        $data['newsletter'] = 1;
        $data['tnc'] = 1;
    }

    // attach form is displayed for the first time or a validation error occured
    $view['title'] = 'Inregistreaza-te!';
    $view['page_name'] = 'register';
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['topnav_select'] = 'register';
    $view['action'] = 'register';
    $view['no_sidebar_login'] = true;
    execute_view_die('views/register.php', $view);
}

?>
