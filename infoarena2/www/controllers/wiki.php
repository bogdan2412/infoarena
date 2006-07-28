<?php

// TODO: This is wrong.
$page_name = join($urlpath, '/');

$action = request('action', 'view');

$page = wikipage_get($page_name);
if (is_null($page)) {
    if ($action == 'view') {
        $action = 'edit';
        $page_content = "Scrie aici despre " . $page_name;
    }
    /*	else if $action {
    // TODO: error message?
    redirect("Home");
    }*/
} else {
    $page_content = $page['text'];
}

// Initialize view.
$view['page_name'] = $page_name;
// Store form data here.
$view['data'] = $data = array();
// Store errors here. By convention the keys in data
// and in error refer to certain fields.
$view['errors'] = $errors = array();

switch ($action) {
    case 'save':
        // Validate data here and place stuff in errors.
        $page_content = getattr($_POST, 'content', "");
        if (strlen($page_content) < 10) {
            $errors['content'] = "Continutul paginii e prea scurt.";
        }
        if (!$errors) {
            wikipage_add_revision($page_name, $page_content, 1);
            flash('Am actualizat continutul');
            redirect(url($page_name));

            break;
        }
        else {
            $view['title'] = "Editare " . $page_name;
            $view['action'] = url($page_name, array('action' => 'save'));
            $data['content'] = $page_content;
            execute_view("views/wikiedit.php", $view);

            break;
        }

    case 'edit':
        // This is the creation action.
        $view['title'] = 'Creare ' . $page_name;
        $view['action'] = url($page_name, array('action' => 'save'));
        $data['content'] = $page_content;
        execute_view("views/wikiedit.php", $view);

        break;

    case 'view':
        // Viewer. Dumbest thing possible.
        $view['title'] = $page_name;
        $view['content'] = $page_content;
        execute_view_die('views/wikiview.php', $view);

    case 'attach-submit':
        // user submitted a file for upload. Process it
        $data['file_name'] = basename($_FILES['file_name']['name']);
        $data['file_size'] = $_FILES['file_name']['size'];
        $view['data'] = $data;
        $view['errors'] = $errors;

        // validate data
        if (!preg_match('/^[a-z0-9\.\-_]+$/i', $data['file_name'])) {
            $errors['file_name'] = 'Nume de fisier invalid (nu folositi '.
                                   'spatii)';
        }                
        if ($data['file_size'] < 0 || $data['file_size'] > IA_ATTACH_MAXSIZE) {
            $errors['file_size'] = 'Fisierul depaseste limita de '
                                   .(IA_ATTACH_MAXSIZE / 1024).' kbytes';
        }
        if (!$errors) {
            // Do the SQL dance.
            $disk_name = attachment_create($data['file_name'],
                                           $data['file_size'],
                                           $page_name, 1);
            // Check if something went wrong.
            if (!isset($disk_name)) {
                $errors['file_name'] = 'Fisierul nu a putut fi atasat';
            }
        }
        if (!$errors) {
            $disk_name = IA_ATTACH_DIR . $disk_name;
            if (!move_uploaded_file($_FILES['file_name']['tmp_name'],
                        $disk_name)) {
                $errors['file_name'] = 'Fisierul nu a putut fi incarcat pe '.
                                       'server'; 
            }
        }
        if (!$errors) {
            flash("Fisierul a fost atasat");
            redirect(url($page_name));
        }
        execute_view_die('views/attachment.php', $view);

    case 'attach':
        // Initial attachment page. Rather empty.
        execute_view_die('views/attachment.php', $view);

    case 'download':
        $file_name = request('file');
        if (!$file_name) {
            flash('Cerere malformata');
            redirect(url($page_name));
        }
        $sql_result = attachment_get($file_name, $page_name);
        if (!$sql_result) {
            flash('Fisierul nu exista.');
            redirect(url($page_name));
        }
        $real_name = IA_ATTACH_DIR . $sql_result['id'];
        $fp = fopen($real_name, 'rb');
        if (!$fp) {
            flash("Nu am gasit fisierul pe server");
            redirect(url($page_name));
            break;
        }
        header("Content-type: application/x-download");
        header("Content-disposition: attachment; filename=".$file_name.";");
        header('Content-Length: ',$sql_result['file_size']);
        fpassthru($fp);
        die();

    case 'delattach':
        $file_name = request('file');
        if (!$file_name) {
            flash('Cerere malformata');
            redirect(url($page_name));
        }
        $sql_result = attachment_get($file_name, $page_name);
        if (!$sql_result) {
            flash('Fisierul nu exista.');
            redirect(url($page_name));
        }
        if (!attachment_delete($file_name, $page_name)) {
            flash('Nu am reusit sa sterg din baza de date.');
            redirect(url($page_name));
        }
        $real_name = IA_ATTACH_DIR.$sql_result['id'];
        if (!unlink($real_name)) {
            flash('Nu am reusit sa sterg fisierul de pe disc.');
            redirect(url($page_name));
        }
        flash('Fisierul '.$file_name.' a fost sters cu succes.');
        redirect(url($page_name));

    case 'listattach':
        $view['attach_list'] = attachment_get_all($page_name);
        $view['page_name'] = $page_name;
        execute_view_die('views/listattach.php', $view);
        break;

    default:
        flash('Actiunea nu este valida.');
        redirect(url($page_name));
}
?>
