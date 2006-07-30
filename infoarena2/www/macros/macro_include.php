<?php

// This macro takes a page parameter and includes another wiki page
// Including another page is VERY difficult, because we have to check
// for recursive includes.
//
// FIXME: Separate the inclusion code (and recursion code)?
// This way we could use clean safe inclusion in other macros.
function macro_include($args)
{
    // This uses some black static magic.
    // include_count is the number of recursions in this function.
    // When include_count reaches the maximum level then we set
    // $include_stop to true and quickly kill the whole stack.
    // At the end of the stack rewind we return an error message
    // and set $include_stop
    static $include_count = 0;
    static $include_stop = false;
    ++$include_count;

    if ($include_count > 5) {
        // Start unwinding.
        $include_stop = true;
        --$include_count;
        //echo "hit maximum recursion $include_count <br />";
        return;
    }
    //echo "going in level $include_count $args[page]<br />";

    if (!isset($args['page'])) {
        --$include_count;
        //echo "going out error level $include_count<br/>";
        return make_error_div("Lipseste parameterul page la macro");
    }
    $incname = $args['page'];
    $textblock = textblock_get_revision($incname);
    if ($textblock == null) {
        --$include_count;
        //echo "going out error level $include_count<br/>";
        return make_error_div("Pagina de inclus e inexistenta");
    }
    //echo "calling wiki <br />";
    $res = wiki_process_text($textblock['text'], $textblock['name']);
    //echo "done calling wiki <br />";

    //echo "going out level " .$include_count . "<br />";
    --$include_count;
    // Unwind
    if ($include_stop) {
        if ($include_count == 0) {
            // Stop unwinding. This is the first include.
            $include_stop = false;
            return make_error_div("Prea multe include-uri recursive");
        } else {
            return;
        }
    }
    return $res;
}
?>
