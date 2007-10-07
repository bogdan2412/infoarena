<?php

require_once(IA_ROOT_DIR . "common/db/textblock.php");

// Check if textblock security string is valid
// FIXME: check task/round existence?
function is_textblock_security_descriptor($descriptor)
{
    return preg_match("/^ \s* task: \s* (".IA_RE_TASK_ID.") \s* $/xi", $descriptor) ||
           preg_match("/^ \s* round: \s* (".IA_RE_ROUND_ID.") \s* $/xi", $descriptor) ||
           preg_match('/^ \s* (private|protected|public) \s* $/xi', $descriptor);
}

// Validates a textblock.
// NOTE: this might be incomplete, so don't rely on it exclusively
function textblock_validate($tb) {
    $errors = array();

    // FIXME How to handle this?
    log_assert(is_array($tb), "You didn't even pass an array");

    if (!is_normal_page_name(getattr($tb, 'name', ''))) {
        $errors['name'] = 'Nume de pagina invalid.';
    }

    // FIXME: move this in textblock edit controller
    if (strlen(getattr($tb, 'title', '')) < 1) {
        $errors['title'] = 'Titlu prea scurt.';
    }

    // FIXME: move this in textblock edit controller
    if (strlen(getattr($tb, 'title', '')) > 64) {
        $errors['title'] = 'Titlu prea lung.';
    }

    if (!is_user_id(getattr($tb, 'user_id'))) {
        $errors['user_id'] = 'ID de utilizator invalid';
    }

    // NOTE: missing timestamp is OK!!!
    // It stands for 'current moment'.
    if (!is_db_date(getattr($tb, 'timestamp', db_date_format()))) {
        $errors['timestamp'] = 'Timestamp invalid.';
    }

    if (!is_db_date(getattr($tb, 'creation_timestamp', db_date_format()))) {
        $errors['creation_timestamp'] = 'Timestamp invalid.';
    }

    if (!is_textblock_security_descriptor(getattr($tb, 'security'))) {
        $errors['security'] = "Descriptor de securitate gresit.";
    }

    return $errors;
}

// This function copies all starting with $srcprefix and copies the over to
// $destprefix.
// It also does template-replacing for everything in $replace, if non-null.
// You can also change the security descriptor on all those files.
//
// Use this like textblock_copy_replace('template/newtask', 'problema/capsuni');
function textblock_copy_replace($srcprefix, $dstprefix, $replace, $security, $user_id)
{
    assert($srcprefix != $dstprefix);
    assert(is_textblock_security_descriptor($security));
    assert(is_whole_number($user_id));

    $textblocks = textblock_get_by_prefix($srcprefix, true, false);
    foreach ($textblocks as $textblock) {
        if ($replace !== null) {
            textblock_template_replace($textblock, $replace);
        }
        if ($replace !== null) {
            $textblock['security'] = $security;
        }
        $textblock['name'] = preg_replace('/^'.preg_quote($srcprefix, '/').'/i', $dstprefix, $textblock['name']);

        //FIXME: hack to keep creation_timestamp correct when textblock already exists
        $first_textblock = textblock_get_revision($textblock['name']);
        if (!$first_textblock) {
            $first_textblock['creation_timestamp'] = null;
        }

        textblock_add_revision($textblock['name'], $textblock['title'],
                $textblock['text'], $user_id, $textblock['security'],
                null, $first_textblock['creation_timestamp']);
    }
}

// Does template replacing in a textblock.
// Replaces all occurences of %key% with value, for all key, value pairs
// in the $replace array.
//
// You should mainly use this horrible painful hack on templates.
//
// MODIFIES $textblock
//
// FIXME: optimize.
function textblock_template_replace(&$textblock, $replace)
{
    foreach ($replace as $key => $value) {
        $textblock['title'] = str_replace("%$key%", $value, $textblock['title']);
        $textblock['text'] = str_replace("%$key%", $value, $textblock['text']);
    }
}

?>
