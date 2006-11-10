<?php

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

// List attachments to a textblock
function controller_attachment_list($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('attach-list', $page);

    $view = array();
    $view['attach_list'] = attachment_get_all($page_name);
    $view['page_name'] = $page['name'];
    $view['title'] = 'Atasamentele paginii '.$page['title'];

    execute_view_die('views/listattach.php', $view);
}

// Create a new attachment to a textblock.
function controller_attachment_create($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('attach-create', $page);

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
    identity_require('attach-create', $page);

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

    // Validate filename.
    if (!attachment_is_valid_name($form_values['file_name'])) {
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

    // determine if attached file is (zip) archive and needs to be extracted
    $autoextract = request('auto_extract') && preg_match('/^.+\.zip$/i', $form_values['file_name']);

    // create attachment list
    $attachments = array();
    if (!$form_errors) {
        if ($autoextract) {
            require_once('controllers/zip_attachment.php');
            $attachments = get_zipped_attachments($_FILES['file_name']['tmp_name']);

            if (false === $attachments) {
                $form_errors['file_name'] = 'Arhiva ZIP este invalida sau nu poate fi recunoscuta';
            }
        }
        else {
            // simple (single) file attachment
            $attachments = array(
                array('name' => $form_values['file_name'], 'size' => $form_values['file_size'],
                      'disk_name' => $_FILES['file_name']['tmp_name'])
            );
        }
    }

    // extract (zip) archive file contents to temporary disk files
    $extract_okcount = 0;
    if (!$form_errors && $autoextract) {
        $ziparchive = $_FILES['file_name']['tmp_name'];

        for ($i = 0; $i < count($attachments); $i++) {
            $att =& $attachments[$i];
            if (isset($att['disk_name']) || !isset($att['zipindex'])) {
                continue;
            }

            // extract archived file to a tempory file on disk
            $tmpname = tempnam(IA_ATTACH_DIR, 'iatmp');
            log_assert($tmpname);
            $res = extract_zipped_attachment($ziparchive, $att['zipindex'], $tmpname);
            if ($res) {
                $att['disk_name'] = $tmpname;
                $extract_okcount++;
            }
        }
    }

    // compute mime type for each file on disk
    if (!$form_errors) {
        $finfo = finfo_open(FILEINFO_MIME);
        for ($i = 0; $i < count($attachments); $i++) {
            $att =& $attachments[$i];
            if (!isset($att['disk_name'])) {
                continue;
            }

            $att['type'] = finfo_file($finfo, $att['disk_name']);
        }
        finfo_close($finfo);
    }

    // Create database entries
    $rewrite_count = 0;
    $attach_okcount = 0;
    if (!$form_errors) {
        for ($i = 0; $i < count($attachments); $i++) {
            $file_att =& $attachments[$i];

            if (!isset($file_att['disk_name'])) {
                continue;
            }

            $attach = attachment_get($file_att['name'], $page_name);
            if ($attach) {
                // Attachment already exists, overwrite.
                identity_require('attach-overwrite', $attach);
                attachment_update($attach['id'], $file_att['name'], $file_att['size'],
                                  $file_att['type'], $page_name, $identity_user['id']);
                $rewrite_count++;
            }
            else {
                // New attachment. Insert.
                attachment_insert($file_att['name'], $file_att['size'], $file_att['type'],
                                  $page_name, $identity_user['id']);
            }

            // check if update/insert went ok
            $attach = attachment_get($file_att['name'], $page_name);
            if ($attach) {
                $file_att['attach_id'] = $attach['id'];
                $attach_okcount++;
            }
        }
    }

    // move files from temporary locations to their final storage place
    if (!$form_errors) {
        for ($i = 0; $i < count($attachments); $i++) {
            $file_att =& $attachments[$i];
            if (!isset($file_att['attach_id'])) {
                continue;
            }
            $disk_name = attachment_get_filepath($file_att);
            rename($file_att['disk_name'], $disk_name);
        }
    }

    // custom error message for simple (single) file uploads
    if (!$form_errors && !$autoextract && 0>=$attach_okcount) {
        $form_errors['file_name'] = 'Fisierul nu a putut fi atasat! Eroare necunoscuta ...';
    }

    // display error/confirmation message
    if (!$form_errors) {
        if ($autoextract) {
            $msg = "Am extras si incarcat {$attach_okcount} fisiere.";
            if ($rewrite_count) {
                $msg .= " {$rewrite_count} fisiere vechi au fost rescrise.";
            }
        }
        else {
            if ($rewrite_count) {
                $msg = "Fisierul trimis a fost atasat cu succes. Un atasamant mai vechi a fost rescris";
            }
            else {
                $msg = "Fisierul trimis a fost atasat cu succes";
            }
        }

        log_print("Auto-extracted {$extract_okcount} files; {$attach_okcount} successful attachment uploads");

        flash($msg);
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

    // Delete from disk.
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

    // serve file
    fpassthru($fp);
    fclose($fp);
    die();
}

// Check for attachment validity and proper permissions.
// Does NOT print error message. Instead it returns HTTP 403/404.
//
// Returns attachment model
function try_attachment_get($page_name, $file_name) {
    if (!$file_name) {
        die_http_error();
    }

    // get attachment info
    $attach = attachment_get($file_name, $page_name);
    if (!$attach) {
        die_http_error();
    }

    $real_name = attachment_get_filepath($attach);
    if (!file_exists($real_name)) {
        die_http_error();
    }

    return $attach;
}

// download an attachment
function controller_attachment_download($page_name, $file_name) {
    $attach = try_attachment_get($page_name, $file_name);
    if (!identity_can('attach-download', $attach)) {
        die_http_error();
    }

    // serve attachment with proper mime types
    serve_attachment(attachment_get_filepath($attach), $file_name, $attach['mime_type']);
}

?>
