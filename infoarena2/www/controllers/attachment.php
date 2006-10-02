<?php

// Returns "real file name" (as stored on the file system) for a given
// attachment model instance.
//
// NOTE: You can't just put this into db.php or any other module shared
// with the judge since it`s dependent on the www server setup.
function attachment_get_filepath($attach) {
    return IA_ATTACH_DIR . $attach['id'];
}

// Try to get the textblock model for a certain page.
// If it fails it will flash and redirect
function try_textblock_get($page_name) {
    $page = textblock_get_revision($page_name);
    if (!$page) {
        flash_error('Cerere invalida');
        redirect(url(''));
    }

    return $page;
}

// Check for attachment validity and proper permissions.
// Issue error messages accordingly.
//
// Returns attachment model
function try_attachment_get($page_name, $file_name) {
    if (!$file_name) {
        flash_error('Cerere malformata');
        redirect(url($page_name));
    }

    // get attachment info
    $attach = attachment_get($file_name, $page_name);
    identity_require('attach-download', $attach);
    if (!$attach) {
        flash_error('Atasamentul cerut nu exista.');
        redirect(url($page_name));
    }

    $real_name = attachment_get_filepath($attach);
    if (!file_exists($real_name)) {
        log_warn("Sursa atasamentului {$attach['id']} nu a fost gasita. Ma asteptam sa fie in {$real_name}");
        flash_error("Nu am gasit fisierul cerut pe server.");
        redirect(url($page_name));
        break;
    }

    return $attach;
}

// List attachments to a textblock
function controller_attachment_list($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('textblock-listattach', $page);

    $view = array();
    $view['attach_list'] = attachment_get_all($page_name);
    $view['page_name'] = $page['name'];
    $view['title'] = 'List de atasamente a paginii '.$page['title'];

    execute_view_die('views/listattach.php', $view);
}

// Create a new attachment to a textblock.
function controller_attachment_create($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('textblock-attach', $page);

    // Initial attachment page. Rather empty.
    $view['title'] = 'Ataseaza la pagina '.$page_name;
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
    $view['title'] = 'Ataseaza la pagina '.$page_name;
    $form_values = array();
    $form_errors = array();

    // Process upload data.
    $form_values['file_name'] = basename($_FILES['file_name']['name']);
    $form_values['file_size'] = $_FILES['file_name']['size'];
    // FIXME: we shouldn't rely on the mime-type computed by the user agent
    $form_values['file_type'] = strtolower($_FILES['file_name']['type']);

    // Validate filename.
    // NOTE: We hereby limit file names. No spaces, please. Not that we have
    // a problem with spaces inside URLs. Everything should be (and hopefully is)
    // urlencode()-ed. However, practical experience shows it is hard to work with
    // such file names, mostly due to URLs word-wrapping when inserted in texts,
    // unless, of course, one knows how to properly escape spaces with %20 or +
    if (!preg_match('/^[a-z0-9\.\-_]+$/i', $form_values['file_name'])) {
        $form_errors['file_name'] = 'Nume de fisier invalid'.
                                    '(va rugam sa nu folositi spatii)';
    }                

    // Check min file size. An invalid file results in a size of 0.
    if ($form_values['file_size'] <= 0) {
        $form_errors['file_size'] = 'Fisier invalid';
    }

    // Check max file size.
    if ($form_values['file_size'] > IA_ATTACH_MAXSIZE) {
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
            $disk_name = attachment_update($attach['id'],
                                           $form_values['file_name'],
                                           $form_values['file_size'],
                                           $form_values['file_type'],
                                           $page_name,
                                           $identity_user['id']);
        }
        else {
            // New attachment. Insert.
            $disk_name = attachment_insert($form_values['file_name'],
                                           $form_values['file_size'],
                                           $form_values['file_type'],
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

    $attach = attachment_get($file_name, $page_name);
    identity_require('attach-delete', $attach);
    if (!$attach) {
        flash_error('Fisierul nu exista.');
        redirect(url($page_name));
    }

    // Delete from data base.
    if (!attachment_delete($attach['id'])) {
        flash_error('Nu am reusit sa sterg din baza de date.');
        redirect(url($page_name));
    }

    // Delete from disc.
    $real_name = IA_ATTACH_DIR . $attach['id'];
    if (!unlink($real_name)) {
        flash_error('Nu am reusit sa sterg fisierul de pe disc.');
        redirect(url($page_name));
    }

    // We've got big balls.
    flash('Fisierul '.$file_name.' a fost sters cu succes.');
    redirect(url($page_name));
}

// serve file through HTTP
// WARNING: this function does not return
function serve_attachment($filename, $attachment_name, $mimetype) {
    // open file
    $fp = fopen($filename, "rb");
    log_assert($fp);
    $stat = fstat($fp);

    // HTTP headers
    header("Content-Type: {$mimetype}");
    header("Content-Disposition: inline; filename=".urlencode($attachment_name).";");
    header('Content-Length: ', $stat['size']);

    // service file
    fpassthru($fp);
    fclose($fp);
    die();
}

// download an attachment
function controller_attachment_download($page_name, $file_name) {
    $attach = try_attachment_get($page_name, $file_name);

    // serve attachment with proper mime types
    serve_attachment(attachment_get_filepath($attach), $file_name, $attach['mime_type']);
}

?>
