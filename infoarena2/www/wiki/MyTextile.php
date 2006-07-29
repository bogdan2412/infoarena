<?php

@require_once("Textile.php");
class MyTextile extends Textile {
    var $page_name;

    function MyTextile($page_name, $options = array()) {
        $this->page_name = $page_name;
        Textile::Textile($options);
    }

    // This is called for ==text here== blocks.
    // By default textile passes the text to html unchanged (it has
    // some filter features we don't use. This is sort of bad because
    // you can inject arbritary html.
    function format_block($args)
    {
        $str = (isset($args['text']) ? $args['text'] : '');
        $str = trim($str);

//        return '<b style="color:red">Bad macro</b>';
        $argvalexp = '"(([^"]*("")*)*)"';
        if (preg_match('/^([a-z][a-z0-9_]*)\s*\((\s*
                        (   [a-z][a-z0-9_]* \s*  = \s* '.$argvalexp.' \s* )*
                        )\)$/ix', $str, $matches)) {
            $macro_name = $matches[1];
            $macro_arg_str = trim($matches[2]);
            if (!preg_match_all('/  ([a-z][a-z0-9_]*) \s*=\s* '.$argvalexp.' \s* /ix',
                        $macro_arg_str, $matches, PREG_SET_ORDER)) {
                $matches = array();
            }
            /*echo '<pre>';
            print_r($matches);
            echo '</pre>';*/
            $args = array('page_name' => $this->page_name);
            for ($i = 0; $i < count($matches); ++$i) {
                if (isset($args[$matches[$i][1]])) {
                    return make_error_div('Duplicate argument '.
                            $matches[$i][1]." for macro $macro_name.");
                }
                $args[$matches[$i][1]] = str_replace('""', '"', $matches[$i][2]);
            }

/*            $res = "$macro_name(";
            foreach ($args as $k => $v) {
                $res .= " ".$k." = \"".$v."\" ";
            }
            $res .= ")";*/

            return execute_macro($macro_name, $args);
        }
        return make_error_div('Bad macro "'.$str.'"');
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

    // Image magic.
    function format_image($args) {
        $srcpath = $args['src'];

        if (strlen($srcpath) > 1 && $srcpath[0] == '?') {
            $file_name = substr($srcpath, 1);
            $args['src'] = url($this->page_name,
                    array('action' => 'download', 'file' => $file_name)); 
            $args['pre'] = "<div><h1>imagine</h1>";
            $args['post'] = "</div>";
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
