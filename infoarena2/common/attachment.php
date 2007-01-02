<?php
require_once(IA_ROOT . "common/db/attachment.php");

// Validates an attachment struct.
function attachment_validate($att) {
    $errors = array();

    // FIXME How to handle this?
    log_assert(is_array($att), "You didn't even pass an array");

    if (!is_normal_page_name(getattr($att, 'page', ''))) {
        $errors['page'] = 'Nume de pagina invalid.';
    }

    if (!is_attachment_name(getattr($att, 'name', ''))) {
        $errors['page'] = 'Nume de pagina invalid.';
    }

    if (!is_user_id(getattr($att, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid';
    }

    if (!is_string(getattr($att, 'mime_type', 'text/plain'))) {
        $errors['mime_type'] = 'Bad mime type';
    }

    // NOTE: missing timestamp is OK!!!
    // It stands for 'current moment'.
    if (!is_db_date(getattr($att, 'timestamp', db_date_format()))) {
        $errors['timestamp'] = 'Timestamp invalid.';
    }

    return $errors;
}

?>
