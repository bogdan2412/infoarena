<?php

require_once __DIR__ . '/../format/pager.php';
require_once __DIR__ . '/../../common/db/textblock.php';
require_once __DIR__ . '/../../common/db/attachment.php';
require_once __DIR__ . '/zip_attachment.php';
require_once __DIR__ . '/../../lib/third-party/zipfile.php';
require_once __DIR__ . '/../../common/avatar.php';

// Try to get the textblock model for a certain page.
function try_textblock_get($page_name) {
  $page = textblock_get_revision($page_name);
  if (!$page) {
    FlashMessage::addError('Cerere invalidă');
    Util::redirectToHome();
  }

  return $page;
}

// List attachments to a textblock
function controller_attachment_list($page_name) {
  $page = try_textblock_get($page_name);
  Identity::enforceViewTextblock($page);

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
  $view['title'] = 'Atașamentele paginii '.$page['title'];
  $view['total_entries'] = attachment_get_count($page_name);
  $view['first_entry'] = $options['first_entry'];
  $view['display_entries'] = $options['display_entries'];

  execute_view_die('views/listattach.php', $view);
}

// Create a new attachment to a textblock.
function controller_attachment_create($page_name) {
  if (Request::isPost()) {
    controller_attachment_submit($page_name);
    die();
  }
  $page = try_textblock_get($page_name);
  Identity::enforceEditTextblockReversibly($page);

  // Initial attachment page. Rather empty.
  $view = array();
  $view['title'] = 'Atașează la pagina '.$page_name;
  $view['page_name'] = $page_name;
  $view['form_values'] = array();
  $view['form_errors'] = array();
  execute_view_die('views/attachment.php', $view);
}

// Submit an attachment to a textblock.
function controller_attachment_submit($page_name) {
  $page = try_textblock_get($page_name);
  Identity::enforceEditTextblockReversibly($page);

  // Create view objects.
  $view = array();
  $view['page_name'] = $page_name;
  $view['title'] = 'Atașează la pagina '.$page_name;
  $form_values = array();
  $form_errors = array();

  if (!isset($_FILES['files']) && is_array($_FILES['files'])) {
    FlashMessage::addError("Eroare! Nu am putut atașa fișierul.");
    redirect(url_attachment_new($page_name));
  }

  if (count($_FILES['files']['name']) > 1 && request('autoextract', false)) {
    FlashMessage::addError("Eroare! Numai un singur fișier ZIP poate fi expandat.");
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
      $form_errors['files'][] = 'Nume de fișier invalid: '
        . html_escape($file_name)
        . '. Nu se pot folosi spații.';
    }

    // Check min file size. An invalid file results in a size of 0.
    if ($file_size <= 0) {
      $form_errors['file_size'][] = 'Fișierul: '
        . html_escape($file_name)
        . ' este invalid';
    }

    // Check max file size.
    if ($file_size > IA_ATTACH_MAXSIZE) {
      $form_errors['file_size'][] = 'Fișierul: '
        . html_escape($file_name)
        . ' depășește limita de '
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
        $form_errors['files'][] = 'Arhiva ZIP este invalidă sau nu '
          . 'poate fi recunoscută';
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
      $tmpname = tempnam('/tmp', 'iatmp');
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
        $extra_errors .= ' A fost intâlnit un fișier cu numele avatar. ' .
          'Pentru a vă modifica imaginea de profil, vă rugăm ' .
          'folosiți pagina „Contul meu”.';
        continue;
      }

      $attach = Attachment::normalizeAndGetByNamePage($file_att['name'], $page_name);
      if ($attach) {
        // Attachment already exists, overwrite.
        Identity::enforceEditAttachmentIrreversibly($attach);
        attachment_update($attach->id, $file_att['name'],
                          $file_att['size'], $file_att['type'], $page_name,
                          Identity::getId(), remote_ip_info());
        $rewrite_count++;
      }
      else {
        // New attachment. Insert.
        attachment_insert($file_att['name'], $file_att['size'],
                          $file_att['type'], $page_name, Identity::getId(),
                          remote_ip_info());
      }

      // check if update/insert went ok
      $attach = Attachment::normalizeAndGetByNamePage($file_att['name'], $page_name);
      if ($attach) {
        $file_att['attach_id'] = $attach->id;
        $file_att['attach_obj'] = $attach->as_array();
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
    $form_errors['files'][] = 'Fișierul nu a putut fi atașat! Eroare ' .
      'necunoscută.';
  }

  // display error/confirmation message
  if (count($form_errors, COUNT_RECURSIVE) == 2) {
    $msg = 'Am ';
    if ($autoextract) {
      $msg .= 'extras și ';
    }

    if ($attach_okcount == 1) {
      $msg .= 'încărcat un fișier.';
    } else {
      $msg .= "încărcat {$attach_okcount} fișiere.";
    }

    if ($rewrite_count == 1) {
      $msg .= ' Un fișier mai vechi a fost rescris.';
    } else if ($rewrite_count > 1) {
      $msg .= " {$rewrite_count} fișiere mai vechi au fost rescrise.";
    }

    if ($skipped_files == 1) {
      $msg .= ' Un fișier nu a fost dezarhivat deoarece era prea ' .
        'mare sau era invalid.';
    } else if ($skipped_files > 1) {
      $msg .= " {$skipped_files} fișiere nu au fost dezarhivate " .
        'deoarece erau prea mari sau erau invalide.';
    }

    $msg .= $extra_errors;

    FlashMessage::addInfo($msg);
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
  if (!Request::isPost()) {
    FlashMessage::addError("Atașamentul nu a putut fi șters!");
    redirect(url_attachment_list($page_name));
  }

  if (!is_attachment_name($file_name)) {
    FlashMessage::addError('Nume invalid.');
    redirect(url_textblock($page_name));
  }

  $attach = Attachment::normalizeAndGetByNamePage($file_name, $page_name);
  if (!$attach) {
    FlashMessage::addError('Fișierul nu există.');
    redirect(url_textblock($page_name));
  }

  Identity::enforceEditAttachmentIrreversibly($attach);

  // Delete from data base.
  if (!attachment_delete($attach->as_array())) {
    FlashMessage::addError('Nu am reușit să șterg fișierul.');
    redirect(url_textblock($page_name));
  }

  if (!$more_files) {
    FlashMessage::addSuccess('Fișierul '.$file_name.' a fost șters cu succes.');
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
  FlashMessage::addSuccess($deleted . ' fișiere au fost șterse cu succes.');
  redirect(url_textblock($page_name));
}

function controller_attachment_rename($page_name, $old_name, $new_name) {
  if (!Request::isPost()) {
    FlashMessage::addError("Atașamentul nu a putut fi redenumit!");
    redirect(url_attachment_list($page_name));
  }

  if (!is_attachment_name($old_name) || !is_attachment_name($new_name)) {
    FlashMessage::addError('Nume invalid.');
    redirect(url_textblock($page_name));
  }

  // don't be redundant
  if ($old_name == $new_name) {
    redirect(url_attachment_list($page_name));
  }

  if (is_avatar_attachment($old_name, $page_name)) {
    FlashMessage::addError('Atașamentul "avatar" nu poate fi redenumit.');
    redirect(url_textblock($page_name));
  }

  if (is_avatar_attachment($new_name, $page_name)) {
    FlashMessage::addError('Nu puteți numi un atașament "avatar". Pentru '
                . 'a vă modifica imaginea de profil vă rugăm folosiți '
                . 'pagina „Contul meu”.');
    redirect(url_textblock($page_name));
  }

  $old_attach = Attachment::normalizeAndGetByNamePage($old_name, $page_name);
  Identity::enforceEditAttachmentIrreversibly($old_attach);
  if (!$old_attach) {
    FlashMessage::addError('Fișierul nu există.');
    redirect(url_textblock($page_name));
  }

  $new_attach = attachment_get($new_name, $page_name);
  if ($new_attach) {
    FlashMessage::addError("Există deja un fișier cu numele $new_name "
                . "atașat paginii $page_name.");
    redirect(url_textblock($page_name));
  };

  // Rename in data base.
  if (!attachment_rename($old_attach->as_array(), $new_name)) {
    FlashMessage::addError('Nu am reușit să redenumesc fișierul.');
    redirect(url_textblock($page_name));
  }

  // Everything went ok
  FlashMessage::addSuccess('Fișierul '.$old_name.' a fost redenumit cu succes în '.$new_name);
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
  $attach = Attachment::normalizeAndGetByNamePage($file_name, $page_name);
  if (!$attach) {
    die_http_error();
  }
  if (!$attach->isViewable()) {
    FlashMessage::addError('Nu aveți permisiuni pentru a descărca fișierul '
                . $file_name);
    redirect(url_textblock($page_name));
  }

  // this is just a check to see if the file exists, we used to have
  // attachments with no actual file on disk
  $real_name = attachment_get_filepath($attach->as_array());
  if (!file_exists($real_name)) {
    die_http_error();
  }

  return $attach->as_array();
}

// download an attachment
function controller_attachment_download($page_name, $file_name,
                                        $restrict_to_safe_mime_types = false) {
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

  http_serve(attachment_get_filepath($attach),
             $file_name,
             $mime_type);
}

// resize and serve an image attachment
function controller_attachment_resized_download(
  string $pageName, string $fileName, string $size) {

  $tb = Textblock::get_by_name($pageName);
  if (!$tb) {
    FlashMessage::addError("Pagina „{$pageName}” nu există.");
    Util::redirectToHome();
  }

  $att = Attachment::normalizeAndGetByNamePage($fileName, $pageName);
  if (!$att) {
    FlashMessage::addError("Fișierul „{$fileName}” nu există.");
    Util::redirectToHome();
  } else if (!$att->isViewable()) {
    FlashMessage::addError("Nu poți vedea fișierul „{$fileName}”.");
    Util::redirectToHome();
  } else if (!$att->isImage()) {
    FlashMessage::addError("Fișierul „{$fileName}” nu este imagine.");
    Util::redirectToHome();
  }

  if (!isset(Config::GEOMETRY[$size])) {
    FlashMessage::addError("Nu înțeleg mărimea „{$size}”.");
    Util::redirectToHome();
  }

  $full = $att->getFileName();
  $resized = Image::resize($full, Config::GEOMETRY[$size]);
  $mimeType = mime_content_type($resized);
  http_serve($resized, $fileName, $mimeType);
}

function controller_attachment_download_zip($page_name, $arguments) {
  $files = array();
  foreach ($arguments as $value) {
    if (is_numeric($value)) {
      $files[] = request($value);
    }
  }
  $zipfile = new zipfile();
  foreach ($files as $filename) {
    $attach = try_attachment_get($page_name, $filename);
    $local_file_path = null;
    $local_file_path = attachment_get_filepath($attach);
    $fh = fopen($local_file_path, "r");
    $contents = fread($fh, filesize($local_file_path));
    $zipfile->add_file($contents, $filename);
  }

  header("Content-type: application/octet-stream");
  header("Content-disposition: attachment; filename=$page_name.zip");
  echo $zipfile->file();
}
