<?php

@require_once("Textile.php");
class MyTextile extends Textile {
    var $page_name;

    function MyTextile($page_name, $options = array()) {
        $this->page_name = $page_name;
        Textile::Textile($options);
    }

    // Override format_link
    // We hook in here to process the url part
    // FIXME: should I do this with format_url?
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
            $args['src'] = url($this->page_name,
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

?>
