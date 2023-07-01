<?php

require_once(IA_ROOT_DIR . 'common/db/user.php');
require_once(IA_ROOT_DIR . 'www/controllers/account_validator.php');
require_once(IA_ROOT_DIR . 'common/db/tokens.php');

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

        // Get data from form an validate it.
        $data['username'] = trim(request('username'));
        $data['password'] = request('password');
        $data['password2'] = request('password2');
        $data['full_name'] = trim(request('full_name'));
        $data['email'] = trim(request('email'));
        $data['tnc'] = (request('tnc') ? 1 : 0);

        pay_tokens(IA_TOKENS_REGISTER);
        $errors = validate_register_data($data);
        // 2. process
        if (count($errors) == 0) {
            // create database entry for user
            $user = user_init();
            $user['username'] = $data['username'];
            $user['password'] = user_hash_password($data['password'], $data['username']);
            $user['full_name'] = $data['full_name'];
            $user['email'] = $data['email'];
            // There are no acceptable errors in user_create.
            user_create($user, remote_ip_info());

            // give user enough tokens to pass login without captcha
            pay_tokens(-IA_TOKENS_LOGIN);
            flash("Felicitări! Contul a fost creat. Acum te poți "
                  ."autentifica.");
            redirect(url_login());
        } else {
            flash_error('Am intâlnit probleme. Verifică datele introduse.');
        }
    } else {
        // form is displayed for the first time. Fill in default values.
        $data['tnc'] = 1;
    }

    if(get_tokens() < IA_TOKENS_REGISTER) {
        $view['captcha'] = recaptcha_get_html(IA_CAPTCHA_PUBLIC_KEY, null,
                true);
    }

    // attach form is displayed for the first time or a validation error occured

    $view['title'] = 'Înregistrează-te!';
    $view['page_name'] = 'register';
    $view['form_errors'] = $errors;
    $view['form_values'] = $data;
    $view['action'] = url_register();
    $view['no_sidebar_login'] = true;
    execute_view_die('views/register.php', $view);
}
