<?php

require_once(IA_ROOT."www/macros/macros.php");
require_once(IA_ROOT."www/identity.php");
require_once(IA_ROOT."www/url.php");
require_once(IA_ROOT."common/textblock.php");
require_once(IA_ROOT."common/cache.php");

// Process textile and returns html with special macro tags.
function wiki_process_textile($content) {
    require_once(IA_ROOT."www/wiki/MyTextile.php");
    log_print("PROCESS TEXTILE");
    $options = array(
            'disable_html' => true,
            'disable_filters' => true,
            'trim_spaces' => false,
            'preserve_spaces' => true,
    );
    $weaver = new MyTextile($options);
    $res = $weaver->process($content);
    unset($weaver);

    return $res;
}

// Used in wiki_process_macros.
function wiki_macro_callback($matches) {
    // We need to parse args again.
    // We can't separate args in the main preg_replace_callback.
    if (!preg_match_all('/
                        ([a-z][a-z0-9_]*)
                        \s* = \s*
                        "((?:[^"]*(?:"")*)*)"
                        /xi', $matches[2], $args, PREG_SET_ORDER)) {
        $args = array();
    }

    $macro_name = $matches[1];
    $macro_args = array();
    for ($i = 0; $i < count($args); ++$i) {
        $argname = strtolower($args[$i][1]);
        $argval = $args[$i][2];
        $macro_args[$argname] = str_replace('""', '"', $argval);
    }
/*    log_print("Exec macro $macro_name");
    log_print_r($macro_args);
    log_print_r($matches);*/
    return execute_macro($macro_name, $macro_args);
}

// Proces macros in content.
function wiki_process_macros($content) {
    require_once(IA_ROOT."www/macros/macros.php");
    return preg_replace_callback(
            '/ <span \s* macro_name="([a-z][a-z0-9_]*)" \s* runas="macro" \s*
                ((?: (?:[a-z][a-z0-9_]*) \s* = \s*
                    "(?:(?:[^"]*(?:"")*)*)" \s* )* \s*)
                ><\/span>/xi', 'wiki_macro_callback', $content);
/*    return preg_replace_callback(
            '/ <?([a-z][a-z0-9_]*) \s*
                ((?: (?:[a-z][a-z0-9_]*) \s* = \s*
                    "(?:(?:[^"]*(?:"")*)*)" \s* )* \s*)
                \?>/xi', 'wiki_macro_callback', $content);*/
}

// No caching, used by JSON.
// Transforms textile into full html with no cache.
// There is no $tb object in JSON, so we're sort of fucked.
function wiki_do_process_text($content) {
    return wiki_process_macros(wiki_process_textile($content));
}

// This processes a big chunk of wiki-formatted text and returns html.
// Note: receives full textblock, not only $text.
// NOTE: Caching does not work with templated textblocks. They suck.
function wiki_process_textblock($tb) {
    log_assert_valid(textblock_validate($tb));

    if (!IA_TEXTILE_CACHE_ENABLE) {
        return wiki_process_macros(wiki_process_textile($tb['text']));
    } else {
        $cacheid = preg_replace('/[^a-z0-9\.\-_]/i', '_', $tb['name']) . '_' .
                   preg_replace('/[^a-z0-9\.\-_]/i', '_', $tb['timestamp']);
        $cache_ret = cache_load($cacheid, null);
        if (is_null($cache_ret)) {
            $cache_ret = wiki_process_textile($tb['text']);
            cache_save($cacheid, $cache_ret);
        }
        return wiki_process_macros($cache_ret);
    }
}

// This is just like wiki_process_text, but it's meant for recursive calling.
// You should use this from macros that include other text blocks.
//
// This returns a html block. That html block can be an error div.
// You can set $cache to false to disable caching, mainly for templates.
function wiki_process_text_recursive($textblock, $cache = true) {
    log_assert_valid(textblock_validate($textblock));

    // This uses some black static magic.
    // include_count is the number of recursions in this function.
    // When include_count reaches the maximum level then we set
    // $include_stop to true and quickly kill the whole stack.
    // At the end of the stack rewind we return an error message
    // and set $include_stop
    static $include_count = 0;
    static $include_stop = false;
    ++$include_count;

    if ($include_count > IA_MAX_RECURSIVE_INCLUDES) {
        // Start unwinding.
        $include_stop = true;
        --$include_count;
        //echo "hit maximum recursion $include_count <br />";
        return;
    }
    //echo "going in level $include_count $args[page]<br />";

    if ($cache) {
        $res = wiki_process_textblock($textblock);
    } else {
        $res = wiki_do_process_text($textblock['text']);
    }

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
