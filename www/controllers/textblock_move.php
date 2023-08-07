<?php

require_once(Config::ROOT . "common/db/textblock.php");

// Initial move controller.
function controller_textblock_move($page_name) {
  // Get actual page.
  $page = textblock_get_revision($page_name);

  // Check permissions.
  if ($page) {
    Identity::enforceMoveTextblock($page);
  } else {
    // Missing page.
    FlashMessage::addError("Pagină inexistentă.");
    Util::redirectToHome();
  }

  $values = array();
  $errors = array();

  if (Request::isPost()) {
    $values['new_name'] = $new_name = request("new_name", "");
    $new_name = normalize_page_name($new_name);

    if (!is_page_name($new_name)) {
      $errors['new_name'] = "Nume de pagină invalid.";
    } else if (textblock_get_revision($new_name) !== null) {
      $errors['new_name'] = "Pagina există deja.";
    }

    if (!$errors) {
      textblock_move($page_name, $new_name);
      FlashMessage::addSuccess("Am mutat pagina.");
      redirect(url_textblock($new_name));
    }
  }

  // -- Print form
  $view = array(
    'title' => "Mută " . $page_name,
    'page_name' => $page_name,
    'action' => url_textblock_move($page_name),
    'form_values' => $values,
    'form_errors' => $errors,
  );

  execute_view_die("views/textblock_move.php", $view);
}

?>
