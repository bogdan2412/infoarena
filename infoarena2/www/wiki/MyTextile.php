<?php

@require_once(IA_ROOT."www/wiki/Textile.php");
require_once(IA_ROOT."www/macros/macros.php");
require_once(IA_ROOT."common/attachment.php");

class MyTextile extends Textile {
    // FIXME: If you see a pointless textile error try tweaking this value.
    private $my_error_reporting = 0xF7F7;

    function MyTextile($options = array()) {
        @Textile::Textile($options);
    }

    // Parse and execute a macro (or return an error div).
    function process_macro($str) {
        //log_print("Processing $str macro");
        //log_backtrace();
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
            $macro_args = array();
            for ($i = 0; $i < count($matches); ++$i) {
                $argname = strtolower($matches[$i][1]);
                $argval = $matches[$i][2];
                if (isset($macro_args[$argname])) {
                    return macro_error("Duplicate argument '$argname' ".
                            "for macro $macro_name.");
                }
                $macro_args[$argname] = str_replace('""', '"', $argval);
            }

            // Black magic here.
            // this function is called from a callback that uses a static variable.
            // Callbacks use static variables because php is retarded.
            // Anyway, execute_macro can use textile for itself, therefore I 
            // have to restore that static variable after execute_macro.
            // This very scary, but it works.
            $res = execute_macro($macro_name, $macro_args);
            @Textile::_current_store($this);
            return $res;
        }
        return macro_error('Bad macro "'.$str.'"');
    }

    // Processes ==$type|$text== blocks.
    function process_pipe_block($type, $text) {
        //log_warn("$type");
        $type = strtolower($type);
        $matches = array();
        // Code!
        if (preg_match('/code\((c|cpp|pas|java)\)/i', $type, $matches)) {
            // syntax highlighting
            $lang = $matches[1];

            if ('c' == $lang) {
                // highlight C as C++
                $lang = 'cpp';
            }
            elseif ('pas' == $lang) {
                // pascal is delphi
                $lang = 'delphi';
            }

            // put javascript dp.SyntaxHighlighter at work
            return "\n<div class=\"code\">"
                   ."<textarea name=\"code\" class=\"{$lang}\" cols=\"60\" rows=\"10\">"
                   .htmlentities($text) . "</textarea></div>\n";
        }
        else {
            return macro_error("Can't handle ==$type| block.");
        }
    }

    // This is called for ==text here== blocks.
    // By default textile passes the text to html unchanged (it has
    // some filter features we don't use. This is sort of bad because
    // you can inject arbritary html.
    function do_format_block($args) {
        $str = getattr($args, 'text', '');
        if (preg_match('/^  \s*  ([a-z][a-z0-9\+\#\-\(\)\.]*)  \s* \|(.*)/sxi', $str, $matches)) {
            return $this->process_pipe_block($matches[1], $matches[2]);
        } else {
            return $this->process_macro($str);
        }
    }

    function is_wiki_link($link) {
        if (preg_match('/^'.IA_RE_EXTERNAL_URL.'$/xi', $link) ||
                (isset($this->links) && isset($this->links[$link]))) {
            return false;
        }
        return true;
    }

    // Override format_link
    // We hook in here to process the url part
    // FIXME: should I do this with format_url?
    function do_format_link($args) {
        $url = getattr($args, 'url', '');
        if ($this->is_wiki_link($url)) {
            if (preg_match("/^ ([^\?]+) \? (".IA_RE_ATTACHMENT_NAME.") $/sxi", $url, $matches)) {
                $args['url'] = url_attachment($matches[1], $matches[2]);
            } else {
                $args['url'] = IA_URL . $url;
            }
        } else {
            $args['clsty'] .= "(wiki_link_external)";
        }
        $res = @parent::format_link($args);

        return $res;
    }

    // Image magic.
    function do_format_image($args) {
        $srcpath = getattr($args, 'src', '');

        $extra = $args['extra'];
        $alt = (preg_match("/\([^\)]+\)/", $extra, $match) ? $match[0] : '');
        $args['extra'] = $alt;
        if (!preg_match('/^'.IA_RE_EXTERNAL_URL.'$/xi', $srcpath)) {
            // Catch internal images.
            if (preg_match('/^ ('.IA_RE_PAGE_NAME.') \? '.
                           '('.IA_RE_ATTACHMENT_NAME.')'.
                           '$/ix', $srcpath, $matches)) {
                $extra = preg_replace('/\([^\)]+\)/', '', $extra, 1);
                $extra = preg_replace('/\s/', '', $extra);
                // FIXME: sometimes we can determine width/height.
                if (!resize_coordinates(100, 100, $extra)) {
                    log_warn("Invalid resize instructions '$extra'");
                    $extra = '';
                }
                $args['src'] = htmlentities(url_image_resize($matches[1], $matches[2], $extra)); 
            }
        }
        //log_print("passing to parent::format image");
        //log_print_r($args);
        $res = @parent::format_image($args);
        return $res;
    }

    // The current error reporting level is saved here.
    private $error_reporting_level = false;

    // Save error_reporting_level.
    function process($content) {
        //log_print("Starting textile");
        $this->error_reporting_level = error_reporting($this->my_error_reporting);
        $res = parent::process($content);
        error_reporting($this->error_reporting_level);
        //log_print("Stopping textile");
        return $res;
    }

    // Wrap around do_format_block, restore errors.
    function format_block($args) {
        if ($this->error_reporting_level === false) {
            return do_format_block($args);
        }
        error_reporting($this->error_reporting_level);

        //log_print("Textile format_block");
        //log_print_r($args);
        //log_backtrace();
        $res = $this->do_format_block($args);
        $res = getattr($args, 'pre', '').$res.getattr($args, 'post', '');
        //log_print("DONE format_block {$args['text']}");

        error_reporting($this->my_error_reporting);
        return $res;
    }

    // Wrap around do_format_link, restore errors.
    function format_link($args) {
        if ($this->error_reporting_level === false) {
            return do_format_link($args);
        }
        error_reporting($this->error_reporting_level);

        //log_print("Textile format_link");
        //log_print_r($args);
        $res = $this->do_format_link($args);
        //log_print("DONE");

        error_reporting($this->my_error_reporting);
        return $res;
    }

    // Disabled, sorry.
    function image_size($filename)
    {
        return null;
    }

    // Wrap around do_format_image, restore errors.
    function format_image($args) {
        if ($this->error_reporting_level === false) {
            return do_format_image($args);
        }
        error_reporting($this->error_reporting_level);

        //log_print("Textile format_image");
        //log_print_r($args);
        $res = $this->do_format_image($args);
        //log_print("DONE");

        error_reporting($this->my_error_reporting);
        return $res;
    }
}

?>
