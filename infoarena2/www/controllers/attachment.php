<?php

// Try to get the sql row for a certain page.
// If it fails it will flash and redirec.t
function try_textblock_get($page_name) {
    $page = textblock_get_revision($page_name);
    if (!$page) {
        flash_error('Cerere invalida');
        redirect(url(''));
    }

    return $page;
}


// List attachments to a textblock.
function controller_attachment_list($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('textblock-listattach', $page);

    $view = array();
    $view['attach_list'] = attachment_get_all($page_name);
    $view['page_name'] = $page['name'];
    $view['page_title'] = $page['title'];

    execute_view_die('views/listattach.php', $view);
}

// Create a new attachment to a textblock.
function controller_attachment_create($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('textblock-attach', $page);

    // Initial attachment page. Rather empty.
    $view['page_name'] = $page_name;
    $view['form_values'] = array();
    $view['form_errors'] = array();
    execute_view_die('views/attachment.php', $view);
}

// Submit an attachment to a textblock.
function controller_attachment_submit($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('textblock-attach', $page);

    global $identity_user;

    // Create view objects.
    $view = array();
    $view['page_name'] = $page_name;
    $form_values = array();
    $form_errors = array();

    // Process upload data.
    $form_values['file_name'] = basename($_FILES['file_name']['name']);
    $form_values['file_size'] = $_FILES['file_name']['size'];

    // Validate filename. This limits attachment names, and it sucks.
    if (!preg_match('/^[a-z0-9\.\-_]+$/i', $form_values['file_name'])) {
        $form_errors['file_name'] = 'Nume de fisier invalid'.
                                    '(nu folositi spatii)';
    }                

    // Check file size.
    if ($form_values['file_size'] < 0 ||
        $form_values['file_size'] > IA_ATTACH_MAXSIZE) {
        $form_errors['file_size'] = 'Fisierul depaseste limita de ' .
            (IA_ATTACH_MAXSIZE / 1024).' kbytes';
    }

    // Validation done, start the SQL monkey.
    
    // Add the file to the database.
    if (!$form_errors) {
        $attach = attachment_get($form_values['file_name'], $page_name);
        if ($attach) {
            // Attachment already exists, overwrite.
            identity_require('attach-overwrite', $attach);
            $disk_name = attachment_update($form_values['file_name'],
                                           $form_values['file_size'],
                                           $page_name,
                                           $identity_user['id']);
        }
        else {
            // New attachment. Insert.
            $disk_name = attachment_insert($form_values['file_name'],
                                           $form_values['file_size'],
                                           $page_name,
                                           $identity_user['id']);
        }
                                       
        // Check if something went wrong.
        if (!isset($disk_name)) {
            // FIXME: do flash here?
            $form_errors['file_name'] = 'Fisierul nu a putut fi atasat';
        }
    }

    // Write the file on disk.
    if (!$form_errors) {
        $disk_name = IA_ATTACH_DIR . $disk_name;
        if (!move_uploaded_file($_FILES['file_name']['tmp_name'],
                    $disk_name)) {
            $form_errors['file_name'] = 'Fisierul nu a putut fi '.
                                        'incarcat pe server'; 
        }
    }

    // Hooray, no error, flash ok
    if (!$form_errors) {
        flash("Fisierul a fost atasat");
        redirect(url($page_name));
    }

    // Errors, print view template.
    $view['form_errors'] = $form_errors;
    $view['form_values'] = $form_values;
    execute_view_die('views/attachment.php', $view);
}

// Delete an attachment.
// FIXME: we get the file from _GET. Add it as a parameter.
function controller_attachment_delete($page_name) {
    // FIXME: parameter.
    $file_name = request('file');
    if (!$file_name) {
        flash_error('Cerere malformata');
        redirect(url($page_name));
    }

    $sql_result = attachment_get($file_name, $page_name);
    identity_require('attach-delete', $sql_result);
    if (!$sql_result) {
        flash_error('Fisierul nu exista.');
        redirect(url($page_name));
    }

    // Delete from data base.
    if (!attachment_delete($file_name, $page_name)) {
        flash_error('Nu am reusit sa sterg din baza de date.');
        redirect(url($page_name));
    }

    // Delete from disc.
    $real_name = IA_ATTACH_DIR.$sql_result['id'];
    if (!unlink($real_name)) {
        flash_error('Nu am reusit sa sterg fisierul de pe disc.');
        redirect(url($page_name));
    }

    // We've got big balls.
    flash('Fisierul '.$file_name.' a fost sters cu succes.');
    redirect(url($page_name));
}

// Download an attachment.
// FIXME: we get the file from _GET. Add it as a parameter.
function controller_attachment_download($page_name) {
    // FIXME: parameter.
    $file_name = request('file');
    if (!$file_name) {
        flash_error('Cerere malformata');
        redirect(url($page_name));
    }

    // Get the actual file.
    $sql_result = attachment_get($file_name, $page_name);
    identity_require('attach-download', $sql_result);
    if (!$sql_result) {
        flash_error('Fisierul nu exista.');
        redirect(url($page_name));
    }
    $real_name = IA_ATTACH_DIR . $sql_result['id'];
    $fp = fopen($real_name, 'rb');
    if (!$fp) {
        flash_error("Nu am gasit fisierul pe server");
        redirect(url($page_name));
        break;
    }

    // HTTP magic goes here..
    header("Content-Type: application/force-download");
    header("Content-disposition: attachment; filename=".$file_name.";");
    header('Content-Length: ',$sql_result['size']);
    fpassthru($fp);
    die();
}

?>
