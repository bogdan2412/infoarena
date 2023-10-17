<?php

require_once(Config::ROOT . 'lib/third-party/Netcarver/Textile/DataBag.php');
require_once(Config::ROOT . 'lib/third-party/Netcarver/Textile/Parser.php');
require_once(Config::ROOT . 'lib/third-party/Netcarver/Textile/Tag.php');
require_once(Config::ROOT . 'common/attachment.php');
require_once(Config::ROOT . 'common/string.php');
require_once(Config::ROOT . 'www/utilities.php');
require_once(Config::ROOT . 'www/url.php');
class MyTextile extends \Netcarver\Textile\Parser {

  const JAVASCRIPT_EVENTS = [
    // form events
    'onblur', 'onchange', 'oncontextmenu', 'onfocus', 'oninput', 'oninvalid',
    'onreset', 'onsearch', 'onselect', 'onsubmit',

    // keyboard events
    'onkeydown', 'onkeypress', 'onkeyup',

    // mouse events
    'onclick', 'ondblclick', 'onmousedown', 'onmousemove', 'onmouseout',
    'onmouseover', 'onmouseup', 'onmousewheel', 'onwheel',

    // drag events
    'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover',
    'ondragstart', 'ondrop', 'onscroll',

    // clipboard events
    'oncopy', 'oncut', 'onpaste',
  ];
  const REJECTION_MESSAGE =
    '<div class="rejected-textile">' .
    '  Your input was rejected because it contains Javascript code.' .
    '  The offending substring(s) were «<strong>%s</strong>».' .
    '</div>';

  function __construct($doctype = 'xhtml') {
    parent::__construct($doctype);
    $this->span_tags['$'] = 'var';
  }

  // Parse and execute a macro (or return an error div).
  function process_macro($str) {
    $str = trim($str);
    $argvalexp = '"(([^"]*("")*)*)"';
    $matches = array();
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
        $macro_args[$argname] = $argval;
      }

      $res = "<span macro_name=\"$macro_name\" runas=\"macro\"";
      foreach ($macro_args as $k => $v) {
        $res .= " $k=\"".$v."\"";
      }
      $res .= "></span>";
      return $res;
    }
    return macro_error('Bad macro "'.$str.'"');
  }

  // Processes ==$type|$text== blocks.
  function process_pipe_block($type, $text) {
    $type = strtolower($type);
    $matches = array();
    if (preg_match('/code\((c|cpp|pas|java|python)\)/i', $type, $matches)) {
      $lang = $matches[1];

      if ('c' == $lang) {
        $lang = 'cpp';
      } elseif ('pas' == $lang) {
        $lang = 'delphi';
      }

      return "\n<div class=\"code\">"
        ."<textarea class=\"brush: {$lang};\" cols=\"60\" rows=\"10\">"
        .html_escape($text) . "</textarea></div>\n";
    }
    else {
      return macro_error("Can't handle ==$type| block.");
    }
  }

  // This is called for ==text here== blocks.
  // By default textile passes the text to html unchanged (it has
  // some filter features we don't use. This is sort of bad because
  // you can inject arbritary html.
  function fTextile($args) {
    $str = getattr($args, 'content', '');
    $matches = array();
    if (preg_match('/^  \s*  ([a-z][a-z0-9\+\#\-\(\)\.]*)  \s* \|(.*)/sxi',
                   $str, $matches)) {
      $res = $this->process_pipe_block($matches[1], $matches[2]);
    } else {
      $res = $this->process_macro($str);
    }

    $res = getattr($args, 'before', '') . $res . getattr($args, 'after', '');
    return $res;
  }

  function is_wiki_link($link) {
    if (preg_match('/^'.IA_RE_EXTERNAL_URL.'$/xi', $link) ||
        (isset($this->links) && isset($this->links[$link]))) {
      return false;
    }
    return true;
  }

  // Prepends a Textile-formatted CSS class to $inner.
  function add_css_class(string $inner, string $class): string {
    if (starts_with($inner, '(')) {
      return '(' . $class . ' ' . substr($inner, 1);
    } else {
      return '(' . $class . ')' . $inner;
    }
  }

  // Override format_link
  // We hook in here to process the url part
  // FIXME: should I do this with format_url?
  function fLink($args) {
    $url = getattr($args, 'urlx', '');
    if ($this->is_wiki_link($url)) {
      $matches = array();
      if (preg_match("/^ ([^\?]+) \? (".IA_RE_ATTACHMENT_NAME.") $/sxi", $url, $matches)) {
        $args['urlx'] = url_attachment($matches[1], $matches[2]);
      } else {
        $args['urlx'] = Config::URL_PREFIX . $url;
      }
    } else {
      $args['inner'] = $this->add_css_class($args['inner'], 'wiki_link_external');
    }
    $res = parent::fLink($args);

    return $res;
  }

  function fImage($args) {
    $srcpath = getattr($args, 'url', '');

    $extra = $args['title'] ?? '';
    $match = array();
    $alt = (preg_match("/\([^\)]+\)/", $extra, $match) ? $match[0] : '');
    $args['extra'] = $alt;

    // To avoid CSRF exploits we restrict all images to textblock attachments
    $allowed = false;
    // $allowed_urls are exceptions to this rule
    $allowed_urls = array("static/images/");

    foreach ($allowed_urls as $url) {
      if (starts_with(strtolower($srcpath), Config::URL_HOST . Config::URL_PREFIX . $url) ||
          starts_with(strtolower($srcpath), Config::URL_PREFIX . $url)) {
        $allowed = true;
        break;
      }
    }

    // Catch internal images
    if (!preg_match('/^'.IA_RE_EXTERNAL_URL.'$/xi', $srcpath)) {
      $matches = array();
      if (preg_match('/^ ('.IA_RE_PAGE_NAME.') \? '.
                     '('.IA_RE_ATTACHMENT_NAME.')'.
                     '$/ix', $srcpath, $matches)) {
        $extra = preg_replace('/\([^\)]+\)/', '', $extra, 1);
        $extra = preg_replace('/\s/', '', $extra);
        // FIXME: sometimes we can determine width/height.
        if (!resize_coordinates(100, 100, $extra)) {
          //log_warn("Invalid resize instructions '$extra'");
          $extra = '';
        }
        $args['url'] = url_absolute(url_image_resize($matches[1], $matches[2], $extra));
        $allowed = true;
      }
    }

    if (!$allowed) {
      return macro_error("Imaginile trebuie să fie atașate unei pagini.");
    }
    $res = parent::fImage($args);
    return $res;
  }

  function rejectJavaScript(string $html): string {
    $joined = implode('|', self::JAVASCRIPT_EVENTS);
    $pattern = sprintf('/\W(%s)\W/', $joined);
    preg_match_all($pattern, $html, $matches);
    $offenders = $matches[1];

    if (strpos($html, '<script') !== false) { // no word boundary check here
      $offenders[] = '&lt;script';
    }

    if (count($offenders)) {
      $joinedOffenders = implode(', ', $offenders);
      return sprintf(self::REJECTION_MESSAGE, $joinedOffenders);
    } else {
      return $html;
    }
  }

  function parse($text): string {
    parent::setSymbol('ellipsis', false); // Do not replace ... with …
    $html = parent::parse($text);
    $html = $this->rejectJavaScript($html);
    return $html;
  }
}
