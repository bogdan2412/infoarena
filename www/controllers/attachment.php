<?php

require_once(IA_ROOT_DIR."www/format/pager.php");
require_once(IA_ROOT_DIR."common/db/textblock.php");
require_once(IA_ROOT_DIR."common/db/attachment.php");
require_once(IA_ROOT_DIR.'www/controllers/zip_attachment.php');
require_once(IA_ROOT_DIR."common/external_libs/zipfile.php");
require_once(IA_ROOT_DIR."common/avatar.php");

// Try to get the textblock model for a certain page.
// If it fails it will flash and redirect
function try_textblock_get($page_name) {
    $page = textblock_get_revision($page_name);
    if (!$page) {
        flash_error('Cerere invalida');
        redirect(url_home());
    }

    return $page;
}

// List attachments to a textblock
function controller_attachment_list($page_name) {
    $page = try_textblock_get($page_name);
    identity_require('textblock-list-attach', $page);

    $view = array();
    $options = pager_init_options();

    $attach_list = attachment_get_all($page_name, "%", $options['first_entry'],
                                      $options['display_entries']);
    //FIXME: hack for numbering
    for ($i = 0; $i < count($attach_list); ++$i) {
        $attach_list[$i]['id'] = $options['first_entry'] + $i + 1;
    }
    $view['attach_list'] = $attach_list;
    $view['page_name'] = $page['name'];
    $view['title'] = 'Atasamentele paginii '.$page['title'];
    $view['total_entries'] = attachment_get_count($page_name);
    $view['first_entry'] = $options['first_entry'];
    $view['display_entries'] = $options['display_entries'];

    execute_view_die('views/listattach.php', $view);
}

// Create a new attachment to a textblock.
function controller_attachment_create($page_name) {
    if (request_is_post()) {
        controller_attachment_submit($page_name);
        die();
    }
    $page = try_textblock_get($page_name);
    identity_require('textblock-attach', $page);

    // Initial attachment page. Rather empty.
    $view = array();
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

    if (!isset($_FILES['files']) && is_array($_FILES['files'])) {
        flash_error("Eroare! Nu s-a putut atasa fisierul.");
        redirect(url_attachment_new($page_name));
    }

    if (count($_FILES['files']['name']) > 1 && request('autoextract', false)) {
        flash_error("Eroare! Numai un singur fisier ZIP poate fi "
                              . "expandat");
        redirect(url_attachment_new($page_name));
    }

    $file_count = count($_FILES['files']['name']);
    $form_errors['files'] = array();
    $form_errors['file_size'] = array();

    for ($i = 0; $i < $file_count; ++$i) {
        $file_name = $_FILES['files']['name'][$i];
        $file_size = $_FILES['files']['size'][$i];

        // Validate filename(s).
        if (!is_attachment_name($file_name)) {
            $form_errors['files'][] = 'Nume de fisier invalid: '
                                . html_escape($file_name)
                                . '. Nu se pot folosi spatii.';
        }

        // Check min file size. An invalid file results in a size of 0.
        if ($file_size <= 0) {
            $form_errors['file_size'][] = 'Fisierul: '
                                    . html_escape($file_name)
                                    . ' este invalid';
        }

        // Check max file size.
        if ($file_size > IA_ATTACH_MAXSIZE) {
            $form_errors['file_size'][] = 'Fisierul: '
                                    . html_escape($file_name)
                                    . ' depaseste limita de '
                                    . (IA_ATTACH_MAXSIZE / 1024 / 1024).'MB';
        }
    }

    // We have to extract a zip, let's hope for the best
    $attachments = array();
    $autoextract = false;
    if (count($form_errors, COUNT_RECURSIVE) == 2) {
        if (request('autoextract', false)
            &&  preg_match('/^.+\.zip$/i', $_FILES['files']['name'][0])) {
                $autoextract = true;
             $zip_files = get_zipped_attachments(
                    $_FILES['files']['tmp_name'][0]);
            if (false === $zip_files) {
                $form_errors['files'][] = 'Arhiva ZIP este invalida sau nu '
                        . 'poate fi recunoscuta';
            } else {
                $attachments = $zip_files['attachments'];
                $skipped_files = $zip_files['total_files'] -
                        count($attachments);
            }
        } else {
            // Multiple attachments
            for ($i = 0; $i < $file_count; ++$i) {
                $attachments[] =
                    array('name' => $_FILES['files']['name'][$i],
                          'size' => $_FILES['files']['size'][$i],
                          'disk_name' => $_FILES['files']['tmp_name'][$i]);
            }
            $skipped_files = 0;
        }
    }

    // extract (zip) archive file contents to temporary disk files
    $extract_okcount = 0;
    if (count($form_errors, COUNT_RECURSIVE) == 2 && $autoextract) {
        $ziparchive = $_FILES['files']['tmp_name'][0];

        for ($i = 0; $i < count($attachments); $i++) {
            $att = & $attachments[$i];
            if (isset($att['disk_name']) || !isset($att['zipindex'])) {
                continue;
            }

            // extract archived file to a tempory file on disk
            $tmpname = tempnam(IA_ROOT_DIR . 'attach/', 'iatmp');
            log_assert($tmpname);
            $res = extract_zipped_attachment($ziparchive, $att['zipindex'],
                    $tmpname);
            if ($res) {
                $att['disk_name'] = $tmpname;
                $extract_okcount++;
            }
        }
    }

    // compute mime type for each file on disk
    if (count($form_errors, COUNT_RECURSIVE) == 2) {
        for ($i = 0; $i < count($attachments); $i++) {
            $att = & $attachments[$i];
            if (!isset($att['disk_name'])) {
                continue;
            }

            $att['type'] = get_mime_type($att['disk_name']);
        }
    }

    // Create database entries
    $rewrite_count = 0;
    $attach_okcount = 0;
    $extra_errors = '';
    if (count($form_errors, COUNT_RECURSIVE) == 2) {
        for ($i = 0; $i < count($attachments); $i++) {
            $file_att = & $attachments[$i];

            if (!isset($file_att['disk_name'])) {
                continue;
            }

            if (is_textfile($file_att['type'])) {
                dos_to_unix($file_att['disk_name']);
                if (is_grader_testfile($file_att['name']) &&
                    is_problem_page($page_name)) {
                    add_ending_newline($file_att['disk_name']);
                }
                $file_att['size'] = filesize($file_att['disk_name']);
            }

            if (is_avatar_attachment($file_att['name'], $page_name)) {
                if (isset($skipped_files)) {
                    $skipped_files++;
                }
                $extra_errors .= ' A fost intalnit un fisier cu numele avatar' .
                        '. Pentru a va modifica imaginea de profil va rugam ' .
                        'folositi pagina "Contul meu".';
                 continue;
            }

            $attach = attachment_get($file_att['name'], $page_name);
            if ($attach) {
                // Attachment already exists, overwrite.
                identity_require('attach-overwrite', $attach);
                attachment_update($attach['id'], $file_att['name'],
                        $file_att['size'], $file_att['type'], $page_name,
                        $identity_user['id'], remote_ip_info());
                $rewrite_count++;
            }
            else {
                // New attachment. Insert.
                attachment_insert($file_att['name'], $file_att['size'],
                        $file_att['type'], $page_name, $identity_user['id'],
                        remote_ip_info());
            }

            // check if update/insert went ok
            $attach = attachment_get($file_att['name'], $page_name);
            if ($attach) {
                $file_att['attach_id'] = $attach['id'];
                $file_att['attach_obj'] = $attach;
                $attach_okcount++;
            }
        }
    }
    // move files from temporary locations to their final storage place
    if (count($form_errors, COUNT_RECURSIVE) == 2) {
        for ($i = 0; $i < count($attachments); $i++) {
            $file_att = & $attachments[$i];
            if (!isset($file_att['attach_id'])) {
                continue;
            }
            $disk_name = attachment_get_filepath($file_att['attach_obj']);
            if (is_uploaded_file($file_att['disk_name'])) {
                $move_ok = move_uploaded_file($file_att['disk_name'],
                        $disk_name);
            } else {
                $move_ok = @rename($file_att['disk_name'], $disk_name);
            }
            if (!$move_ok) {
                log_error("Failed moving attachment to final storage ".
                    "(from {$file_att['disk_name']} to $disk_name)");
            }
            if (!chmod($disk_name, 0640)) {
                log_error("Failed setting attachment permissions ".
                    "(target $disk_name)");
            }
        }
    }

    // custom error message for simple (single) file uploads
    if (!$form_errors && !$autoextract && 0 >= $attach_okcount &&
                !$extra_errors) {
        $form_errors['files'][] = 'Fisierul nu a putut fi atasat! Eroare ' .
                'necunoscuta ...';
    }

    // display error/confirmation message
    if (count($form_errors, COUNT_RECURSIVE) == 2) {
        $msg = 'Am ';
        if ($autoextract) {
            $msg .= 'extras si ';
        }

        if ($attach_okcount == 1) {
            $msg .= 'incarcat un fisier.';
        } else {
            $msg .= "incarcat {$attach_okcount} fisiere.";
        }

        if ($rewrite_count == 1) {
            $msg .= ' Un fisier mai vechi a fost rescris.';
        } else if ($rewrite_count > 1) {
            $msg .= " {$rewrite_count} fisiere mai vechi au fost rescrise.";
        }

        if ($skipped_files == 1) {
            $msg .= ' Un fisier nu a fost dezarhivat deoarece era prea ' .
                    'mare sau era invalid.';
        } else if ($skipped_files > 1) {
            $msg .= " {$skipped_files} fisiere nu au fost dezarhivate " .
                    'deoarece erau prea mari sau erau invalide.';
        }

        $msg .= $extra_errors;

        flash($msg);
        redirect(url_textblock($page_name));
    }

    if ($extra_errors) {
        $form_errors['files'][] = $extra_errors;
    }

    // Errors, print view template.
    $view['form_errors'] = $form_errors;
    $view['form_values'] = $form_values;
    execute_view_die('views/attachment.php', $view);
}

// Delete an attachment.
function controller_attachment_delete($page_name, $file_name, $more_files = 0) {
    if (!request_is_post()) {
        flash_error("Atasamentul nu a putut fi sters!");
        redirect(url_attachment_list($page_name));
    }

    if (!is_attachment_name($file_name)) {
        flash_error('Nume invalid.');
        redirect(url_textblock($page_name));
    }

    $attach = attachment_get($file_name, $page_name);
    identity_require('attach-delete', $attach);
    if (!$attach) {
        flash_error('Fisierul nu exista.');
        redirect(url_textblock($page_name));
    }

    // Delete from data base.
    if (!attachment_delete($attach)) {
        flash_error('Nu am reusit sa sterg fisierul.');
        redirect(url_textblock($page_name));
    }

    // Delete the resizedimages in case the page is an avatar
    $matches = get_page_user_name($page_name);
    if (is_avatar_attachment($file_name, $page_name)) {
        avatar_delete($matches[1]);
    }
    // We've got big balls.

    if (!$more_files) {
        flash('Fisierul '.$file_name.' a fost sters cu succes.');
        redirect(url_textblock($page_name));
    } else {
        return 1;
    }
}

// Delete more attachments
function controller_attachment_delete_many($page_name, $arguments) {
    $files = array();
    $deleted = 0;

    foreach ($arguments as $value) {
        if (is_numeric($value)) {
            $files[] = request($value);
        }
    }
    foreach ($files as $file_name) {
       $deleted += controller_attachment_delete($page_name, $file_name, 1);
    }
    flash($deleted . ' fisiere au fost sterse cu succes.');
    redirect(url_textblock($page_name));
}

function controller_attachment_rename($page_name, $old_name, $new_name) {
    if (!request_is_post()) {
        flash_error("Atasamentul nu a putut fi redenumit!");
        redirect(url_attachment_list($page_name));
    }

    if (!is_attachment_name($old_name) || !is_attachment_name($new_name)) {
        flash_error('Nume invalid.');
        redirect(url_textblock($page_name));
    }

    // don't be redundant
    if ($old_name == $new_name) {
        redirect(url_attachment_list($page_name));
    }

    if (is_avatar_attachment($old_name, $page_name)) {
        flash_error('Atasamentul "avatar" nu poate fi redenumit.');
        redirect(url_textblock($page_name));
    }

    if (is_avatar_attachment($new_name, $page_name)) {
        flash_error('Nu puteti numi un atasament "avatar". Pentru '
                . 'a va modifica imaginea de profil va rugam folositi '
                . 'pagina "Contul meu".');
        redirect(url_textblock($page_name));
    }

    $old_attach = attachment_get($old_name, $page_name);
    identity_require('attach-rename', $old_attach);
    if (!$old_attach) {
        flash_error('Fisierul nu exista.');
        redirect(url_textblock($page_name));
    }

    $new_attach = attachment_get($new_name, $page_name);
    if ($new_attach) {
        flash_error("Exista deja un fisier cu numele $new_name "
                  . "atasat paginii $page_name.");
        redirect(url_textblock($page_name));
    };

    // Rename in data base.
    if (!attachment_rename($old_attach, $new_name)) {
        flash_error('Nu am reusit sa redenumesc fisierul.');
        redirect(url_textblock($page_name));
    }

    // Everything went ok
    flash('Fisierul '.$old_name.' a fost redenumit cu succes in '.$new_name);
    redirect(url_textblock($page_name));
}

// Does NOT print error message. Instead it returns HTTP 403/404.
//
// Returns attachment model
function try_attachment_get($page_name, $file_name) {
    if (!$file_name || !is_attachment_name($file_name)) {
        die_http_error();
    }

    // get attachment info
    $attach = attachment_get($file_name, $page_name);
    if (!$attach) {
        die_http_error();
    }
    if (!identity_can('attach-download', $attach)) {
        flash_error('Nu aveti permisiuni pentru a descarca fisierul '
                  . $file_name);
        redirect(url_textblock($page_name));
    }

    $real_name = attachment_get_filepath($attach);
    if (!file_exists($real_name)) {
        die_http_error();
    }

    return $attach;
}

// download an attachment
function controller_attachment_download($page_name, $file_name,
                                        $restrict_to_safe_mime_types = false) {
    // referer check
    if (http_referer_check()) {
        $attach = try_attachment_get($page_name, $file_name);

        // serve attachment with proper mime types
        global $IA_SAFE_MIME_TYPES;
        if (in_array($attach['mime_type'], $IA_SAFE_MIME_TYPES)) {
            $mime_type = $attach['mime_type'];
        } else {
            if ($restrict_to_safe_mime_types) {
                die_http_error(403, 'Permission denied');
            }
            $mime_type = "application/octet-stream";
        }
        http_serve(attachment_get_filepath($attach), $file_name, $mime_type);
    } else {
        // redirect to main page
        redirect(url_absolute(url_home()));
    }
}

function controller_attachment_download_zip($page_name, $arguments) {
    if (http_referer_check()) {
        $files = array();
        foreach ($arguments as $value) {
            if (is_numeric($value)) {
                $files[] = request($value);
            }
        }
        $zipfile = new zipfile();
        foreach ($files as $filename) {
            $attach = try_attachment_get($page_name, $filename);
            $local_file_path = attachment_get_filepath($attach);
            $fh = fopen($local_file_path, "r");
            $contents = fread($fh, filesize($local_file_path));
            $zipfile->add_file($contents, $filename);
        }

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=$page_name.zip");
        echo $zipfile->file();
    } else {
        // redirect to main page
        redirect(url_absolute(url_home()));
    }
}
