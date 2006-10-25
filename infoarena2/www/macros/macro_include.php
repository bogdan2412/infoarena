<?php

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
    if ($textblock == null) {
        return macro_error("No such page: $incname");
    }

    // FIXME: OPTIMIZE: This may prove to be a bottleneck when dealing with huge content and a lot of parameters.
    // A better algorithm would construct the resulting content in a single pass.
    $content = $textblock['text'];
    foreach ($args as $key => $val) {
        if ('page' == $key) {
            continue;
        }

        $content = str_replace('%'.$key.'%', $val, $content);
    }

    return wiki_process_text_recursive($content);
}
?>
