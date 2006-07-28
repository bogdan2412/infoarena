<?php

error_reporting(0);
require_once("Textile.php");

class MyTextile extends Textile {
    var $page_name;

    function MyTextile($page_name, $options = array()) {
        $this->page_name = $page_name;
        Textile::Textile($options);
    }

    function format_link($args) {
        if (strlen($args['url']) > 1 && $args['url'][0] == '/') {
            $args['url'] = url(substr($args['url'], 1));
        } else {
            $args['clsty'] .= "(wiki_link_external)";
        }
        $res = parent::format_link($args);

        return $res;
    }

    function format_image($args) {
        $srcpath = $args['src'];
        if (strlen($srcpath) > 1 && $srcpath[0] == '?') {
            $file_name = substr($srcpath, 1);
            $args['src'] = url($page_name,
                    array('action' => 'download', 'file' => $file_name)); 
        } else if (strlen($srcpath) > 1 && $srcpath[0] == '/') {
            $parts = explode('?', substr($srcpath, 1));
            if (count($parts) == 2) {
                $other_page_name = $parts[0];
                $file_name = $parts[1];
                $args['src'] = url($other_page_name,
                        array('action' => 'download', 'file' => $file_name)); 
            }
        }
        //echo "<pre>insrc $srcpath outsrc $args[src]</pre>";
        $res = parent::format_image($args);

        return $res;
    }
}

// This processes a big chunk of wiki-formatted text and returns html.
// The paramaters is an array of usefull information. macros can use them.
function wiki_process_text($wiki_text, $parameters) {
    // TODO: save error_reporting level before resetting it and restore it
    // before return
    $weaver = new MyTextile(getattr($parameters, 'page_name'));
    return $weaver->process($wiki_text);
}
error_reporting(1 << 16 - 1);
?>
