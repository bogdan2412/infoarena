<?php

// page title
$view['title'] = 'Inregistrare';

// here we store validation errors. It is a dictionary, indexed by field names
$errors = array();

// `data` dictionary is a dictionary with data to be displayed by form view
// when displaying the form for the first time, this is filled with
$data = array();

if ('save' == getattr($urlpath, 1, null)) {
    // user submitted registration form. Process it

    // 1. validate data
    $data['full_name'] = getattr($_POST, 'full_name');
    if (3 >= strlen(trim($data['full_name']))) {
        $errors['full_name'] = 'Nu ati completat numele';
    }
    $data['email'] = getattr($_POST, 'email');
    if (!preg_match('/[^@]+@.+\..+/', $data['email'])) {
        $errors['email'] = 'Adresa de e-mail invalida';
    }

    // 2. process
    if (!$errors) {
        // ...
    }
}
else {
    // form is displayed for the first time. Fill in default values.
    $data['email'] = '@';
}


// attach form is displayed for the first time or a validation error occured
$view['errors'] = $errors;
$view['data'] = $data;
include('views/register.php');

?>
