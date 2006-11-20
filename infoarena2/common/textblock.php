<?php

require_once(IA_ROOT . "common/db/textblock.php");

// This function copies all starting with $srcprefix and copies the over to
// $destprefix.
// It also does template-replacing for everything in $replace, if non-null.
// You can also change the security descriptor on all those files.
//
// Use this like textblock_copy_replace('template/newtask', 'problema/capsuni');
function textblock_copy_replace($srcprefix, $dstprefix, $replace, $security, $user_id)
{
    assert($srcprefix != $dstprefix);

    $textblocks = textblock_get_list_by_prefix($srcprefix, true, false);
    foreach ($textblocks as $textblock) {
        if ($replace !== null) {
            textblock_template_replace($textblock, $replace);
        }
        if ($replace !== null) {
            $textblock['security'] = $security;
        }
        $textblock['name'] = preg_replace('/^'.preg_quote($srcprefix, '/').'/i', $dstprefix, $textblock['name']);
        log_print("Adding {$textblock['name']}");
        textblock_add_revision($textblock['name'], $textblock['title'],
                $textblock['text'], $textblock['security'], $user_id);
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
