<?php

require_once(Config::ROOT . "common/db/textblock.php");

// Initial copy controller.
function controller_textblock_copy($page_name) {
  // Get actual page.
  $page = textblock_get_revision($page_name);

  // Check permissions.
  if ($page) {
    Identity::enforceEditTextblockReversibly($page);
  } else {
    // Missing page.
    FlashMessage::addError("Pagină inexistentă.");
    redirect(url_home());
  }

  $values = array();
  $errors = array();

  if (request_is_post()) {
    $values['new_name'] = $new_name = request("new_name", "");
    $new_name = normalize_page_name($new_name);

    if (!is_page_name($new_name)) {
      $errors['new_name'] = "Nume de pagină invalid.";
    } else if (textblock_get_revision($new_name) !== null) {
      $errors['new_name'] = "Pagina există deja.";
    }

    if (!$errors) {
      textblock_copy($page, $new_name, Identity::getId(),
                     remote_ip_info());
      FlashMessage::addSuccess("Am copiat pagina.");
      redirect(url_textblock($new_name));
    }
  }

  // -- Print form
  $view = array(
    'title' => "Copiază " . $page_name,
    'page_name' => $page_name,
    'action' => url_textblock_copy($page_name),
    'form_values' => $values,
    'form_errors' => $errors,
  );

  execute_view_die("views/textblock_copy.php", $view);
}

?>
