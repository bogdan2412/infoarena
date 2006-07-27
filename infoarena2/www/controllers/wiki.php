<?php

// `data` dictionary is a dictionary with data to be displayed by form view
// when displaying the form for the first time, this is filled with
$data = array();

// here we store validation errors. It is a dictionary, indexed by field names
$errors = array();

// TODO: This is wrong.
$page_name = join($urlpath, '/');

$action = request('action', 'view');

$page = wikipage_get($page_name);
if (is_null($page)) {
    if ($action == 'view') {
        $action = 'edit';
        $page_content = "Scrie ba aici despre " . $page_name;
    }
    /*	else if $action {
    // TODO: error message?
    redirect("Home");
    }*/
} else {
    $page_content = $page['text'];
}

switch ($action) {
    case 'save':
        // Validate data here and place stuff in errors.
        $page_content = getattr($_POST, 'content', "");
        if (strlen($page_content) < 10) {
            $errors['content'] = "Scrie ma totusi ceva";
        }
        if (!$errors) {
            wikipage_add_revision($page_name, $page_content, 1);
            redirect(url($page_name));

            break;
        }
        else {
            $view['title'] = "Editare " . $page_name;
            $view['action'] = url($page_name, array('action' => 'save'));
            $data['content'] = $page_content;
            include("views/wikiedit.php");

            break;
        }

    case 'edit':
        // This is the creation action.
        $view['title'] = 'Creare ' . $page_name;
        $view['action'] = url($page_name, array('action' => 'save'));
        $data['content'] = $page_content;
        include('views/wikiedit.php');

        break;

        // View
    case 'view':
        $view['title'] = $page_name;
        $view['page_name'] = $page_name;
        $view['content'] = $page_content;

        include('views/wikiview.php');
        break;

    case 'attach-submit':
        // user submitted a file for upload. Process it
        $data['file_name'] = basename($_FILES['file_name']['name']);
        $data['file_size'] = $_FILES['file_name']['size'];
        $view['page_name'] = $page_name;

        // validate data
        if (!preg_match('/[a-z0-9.\-_]+/i', $data['file_name'])) {
            $errors['file_name'] = 'Nume de fisier invalid (nu folositi '.
                                   'spatii)';
        }                
        if ($data['file_size'] < 0 || $data['file_size'] > IA_ATTACH_MAXSIZE) {
            $errors['file_size'] = 'Fisierul depaseste limita de '
                                   .(IA_ATTACH_MAXSIZE / 1024).' kbytes';
        }
        if (!$errors) {
            // Do the SQL dance.
            $data['real_name'] = attachment_create($data['file_name'],
                                                   $data['file_size'],
                                                   $page_name, 1);
            echo "page name = " . $page_name;
            // Check if something went wrong.
            if (!$data['real_name']) {
                $errors['file_name'] = 'Fisierul nu a putut fi atasat';
            }
        }
        if (!$errors) {
            $data['real_name'] = IA_ATTACH_DIR . $data['real_name'];
            if (!move_uploaded_file($_FILES['file_name']['tmp_name'], 
                                $data['real_name'])) {
                $errors['file_name'] = 'Fisierul nu a putut fi incarcat pe '.
                                       'server'; 
            }
        }
        if (!$errors) {
            redirect(url($page_name));
        }
        include('views/attachment.php');
        break;

    case 'attach':
        $data['max_file_size'] = IA_ATTACH_MAXSIZE;
        $view['page_name'] = $page_name;
        include('views/attachment.php');
        break;

    case 'download':
        $file_name = request('file');
        if (!$file_name) {
            // missing parameter.
            // FLASH
            redirect(url($page_name));
        }
        $sql_result = attachment_get($file_name, $page_name);
        if (!$sql_result) {
            // FLASH
            redirect(url($page_name));
        }
        $real_name = IA_ATTACH_DIR . $sql_result['id'];
        $fp = fopen($real_name, 'rb');
        if (!$fp) {
            // FLASH
            // Fisierul nu exista pe server
            redirect(url($page_name));
            break;
        }
        header("Content-type: application/x-download");
        header("Content-disposition: attachment; filename=".$file_name.";");
        header('Content-Length: ',$sql_result['file_size']);
        fpassthru($fp);
        die();

        break;

    case 'delattach':
        $file_name = request('file');
        if (!$file_name) {
            // Nu e filename.
            redirect(url($page_name));
        }
        $sql_result = attachment_get($file_name, $page);
        if (!$sql_result) {
            // 'Fisierul cerut nu exista in baza de date';
            redirect(url($page_name));
        }
        if (!attachment_delete($file_name, $page)) {
            // Fisierul nu s-a putut sterge din baza de date';
            redirect(url($page_name));
        }
        $real_name = IA_ATTACH_DIR.$sql_result['id'];
        if (!unlink($real_name)) {
            // 'Fisierul nu s-a putut sterge de pe server';
            redirect(url($page_name));
        }
        redirect(url($page_name));
        break;

    default:
        // FLASH: invalid action
        redirect(url($page_name));
}


?>
