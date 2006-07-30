<?php

// This macro takes a page parameter and includes another wiki page
function macro_include($args)
{
    if (!isset($args['page'])) {
        return make_error_div("Lipseste parameterul page la macro");
    }

    $incname = $args['page'];
    $textblock = textblock_get_revision($incname);
    if ($textblock == null) {
        return make_error_div("Pagina de inclus e inexistenta");
    }

    return wiki_process_text_recursive($textblock['text'], $args['context']);
}
?>
