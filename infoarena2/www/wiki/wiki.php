<?php

@require_once("Textile.php");

class MyTextile extends Textile {
    function format_link($args) {
/*        echo '<pre>format_link params <br />';
        print_r($args);
        echo '</pre>';*/

        if (strlen($args['url']) > 1 && $args['url'][0] == '/') {
            $args['url'] = IA_URL . substr($args['url'], 1);
        } else {
            $args['clsty'] .= "(wiki_link_external)";
        }
        $res = parent::format_link($args);

        return $res;
    }
}

// This processes a big chunk of wiki-formatted text and returns html.
// The paramaters is an array of usefull information. macros can use them.
function wiki_process_text($wiki_text, $parameters) {
    // TODO: save error_reporting level before resetting it and restore it
    // before return
    error_reporting(0);
    $weaver = build_weaver();
    return $weaver->process($wiki_text);
}

// Build an instance of the textile processor.
// Config here.
function build_weaver()
{
    return new MyTextile();
}

?>
