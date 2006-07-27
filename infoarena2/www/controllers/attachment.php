<?php

// here we store validation errors. It is a dictionary, indexed by field names
$errors = array();

// `data` dictionary is a dictionary with data to be displayed by form view
// when displaying the form for the first time, this is filled with
$data = array('max_file_size' => IA_ATTACH_MAXSIZE);
if (count($urlpath) < 3) {
    redirect(url(''));
    // massage
}
else {
    $page = join(array_slice($urlpath, 2, count($urlpath)-2), '/');
}

if (!wikipage_get($page)) {
    print_r('N-am gasit frate '.$page);
    redirect(url(''));
   // message
}

// page title
$view['title'] = 'Atasare fisier in '.$page;

switch (getattr($urlpath, 1, null)) {
    case 'save':
        // user submitted a file for upload. Process it
        $data['file_name'] = basename($_FILES['file_name']['name']);
        $data['file_size'] = $_FILES['file_name']['size'];
        
        // validate data
        if (!preg_match('/[a-z0-9.\-_]+/i', $data['file_name'])) {
            $errors['file_name'] = 'Nume de fisier invalid (nu folositi 
                                   spatii)';
        }                
        if ($data['file_size'] < 0 || $data['file_size'] > IA_ATTACH_MAXSIZE) {
            $errors['file_size'] = 'Fisierul depaseste limita de '
                                   .(IA_ATTACH_MAXSIZE/1024).' kbytes';
        }
        if (!$errors) {
            $data['real_name'] = attachment_create($data['file_name'],
                                                   $data['file_size'], $page, 1);
            if (!$data['real_name']) {
                $errors['file_name'] = 'Fisierul nu a putut fi atasat';
                break;
            }
            $data['real_name'] = IA_ATTACH_DIR.$data['real_name'];
            if (!move_uploaded_file($_FILES['file_name']['tmp_name'], 
                                    $data['real_name'])) {
                $errors['file_name'] = 'Fisierul nu a putut fi incarcat pe 
                                       server'; 
                break;
            }
            redirect(url($page));
        }
    break;

    case 'download':
        $data['file_name'] = request('file');
        if (!$data['file_name']) {
            redirect(url(''));
            // message
        }
        if (!preg_match('/[a-z0-9.\-_]+/i', $data['file_name'])) {
            $errors['file_name'] = 'Nume de fisier invalid (nu folositi 
                                   spatii)';
        }
        $data['sql_result'] = attachment_get($data['file_name'], $page);
        if (!$data['sql_result']) {
            $errors['file_name'] = 'Fisierul cerut nu exista in baza de date';
        }
        if (!$errors) {
            $data['real_name'] = IA_ATTACH_DIR.$data['sql_result']['id'];
            $fp = fopen($data['real_name'], 'rb');
            if (!$fp) {
                $errors['file_name'] = 'Fisierul nu exista pe server';
                break;
            }
            header("Content-type: application/x-download");
            header("Content-disposition: attachment; filename=".
                   $data['file_name'].";");
            header('Content-Length: ',$data['sql_result']['size']);
            fpassthru($fp);
            die();
        }
    break;
 
    case 'delete':
    break;
}

// attach form is displayed for the first time or a validation error occured
$view['errors'] = $errors;
$view['data'] = $data;
$view['action'] = $page;
include('views/attachment.php');

?>
