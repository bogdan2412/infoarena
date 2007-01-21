<?php

require_once(IA_ROOT . "common/textblock.php");
require_once(IA_ROOT . "common/db/textblock.php");

// This macro takes a page parameter and includes another wiki page
//
// Additionally it accepts an unlimited number of template parameters. The macro will substitute
// any occurences of form: %tag% with argument value for tag.
//
// NOTE: Substitution occurs before transforming the textile into HTML. In some use cases,
// the substitution may break textile formatting.
// FIXME: Should we escape argument values before inserting them inside templates at the expense of flexibility?
function macro_include($args) {
    if (!isset($args['page'])) {
        return macro_error("Expecting argument `page`");
    }

    $incname = $args['page'];
    $textblock = textblock_get_revision($incname);
    if (is_null($textblock)) {
        return macro_error("No such page: $incname");
    }

    // check permissions
    if (!identity_can('textblock-view', $textblock)) {
        return macro_permission_error();
    }

    $content = $textblock['text'];
    $replace = array();
    foreach ($args as $key => $val) {
        if ('page' != $key) {
            $replace[$key] = $val;
        }
    }
    textblock_template_replace($textblock, $replace);

    return wiki_process_text_recursive($textblock);
}

?>
