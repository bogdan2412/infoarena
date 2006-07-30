<?php

@require_once("Textile.php");

class MyTextile extends Textile {
    // Context variables, set on construction.
    public $context;

    // Page name.
    public $page_name;

    // url for external urls.
    // mailto: and <proto>:// and mail adresses of sorts.
    public $external_url_exp = '/^([a-z]+:\/\/|mailto:[^@]+@[^@]+|[^@]+@[^@])/i';

    function MyTextile($context, $options = array()) {
        if ((!isset($context)) || (!isset($context['page_name']))) {
            print('Bad arguments to mytextile');
            die();
        }
        $this->context = $context;
        $this->page_name = $context['page_name'];
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
            $args = array('context' => $this->context);
            for ($i = 0; $i < count($matches); ++$i) {
                if ($matches[$i][1] == "context") {
                    return make_error_div("Invalid argument name 'context'");
                }
                if (isset($args[$matches[$i][1]])) {
                    return make_error_div('Duplicate argument '.
                            $matches[$i][1]." for macro $macro_name.");
                }
                $args[$matches[$i][1]] = str_replace('""', '"', $matches[$i][2]);
            }

            /*$res = "$macro_name(";
            foreach ($args as $k => $v) {
                $res .= " ".$k." = \"".$v."\" ";
            }
            $res .= ")";
            echo $res;*/

            // Black magic here.
            // this function is called from a callback that uses a static variable.
            // Callbacks use static variables because php is retarded.
            // Anyway, execute_macro can use textile for itself, therefore I 
            // have to restore that static variable after execute_macro.
            // This very scary, but it works.
            $res = execute_macro($macro_name, $args);
            Textile::_current_store($this);
            return $res;
        }
        return make_error_div('Bad macro "'.$str.'"');
    }

    function is_wiki_link($link)
    {
        if (preg_match($this->external_url_exp, $link) ||
                (isset($this->links) && isset($this->links[$link]))) {
            return false;
        }
        return true;
    }

    // Override format_link
    // We hook in here to process the url part
    // FIXME: should I do this with format_url?
    function format_link($args) {
        $url = getattr($args, 'url', '');
        if ($this->is_wiki_link($url)) {
            $args['url'] = url($url);
        } else {
            $args['clsty'] .= "(wiki_link_external)";
        }
        $res = parent::format_link($args);

        return $res;
    }

    // Image magic.
    function format_image($args) {
        $srcpath = getattr($args, 'src', '');

        if (!preg_match($this->external_url_exp, $srcpath)) {
            //echo 'non-external img';
            if (preg_match('/^[a-z0-9\.\-_]+$/i', $srcpath)) {
                //echo 'local attachment';
                $args['src'] = url($this->page_name,
                        array('action' => 'download', 'file' => $srcpath)); 
            } else if (preg_match('/^ ([a-z0-9_\-\/]+) \? ([a-z0-9\.\-_]+)   $/ix', $srcpath, $matches)) {
                //echo 'remote attachment';
                $args['src'] = url($matches[1],
                        array('action' => 'download', 'file' => $matches[2])); 
            }
        }
        //echo "<pre>insrc $srcpath outsrc $args[src]</pre>";
        $res = parent::format_image($args);

        return $res;
    }
}

?>
