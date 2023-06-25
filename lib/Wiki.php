<?php

/**
 * Everything to do with parsing and rendering Textile, LaTex and in-house macros.
 **/

require_once IA_ROOT_DIR . 'common/cache.php';
require_once IA_ROOT_DIR . 'common/textblock.php';
require_once IA_ROOT_DIR . 'www/identity.php';
require_once IA_ROOT_DIR . 'www/macros/macros.php';
require_once IA_ROOT_DIR . 'www/url.php';
require_once IA_ROOT_DIR . 'www/wiki/MyTextile.php';

class Wiki {
  const MAX_RECURSIVE_INCLUDES = 5;
  const MATHJAX_PATTERNS = [
    '/\\\(.+\\\)/',
    '/\\\[.+\\\]/',
    '/\$\$.+\$\$/',
  ];

  private static int $recursionDepth = 0;
  private static bool $hasMathJax = false;

  private static function checkForMathJax(string $s): void {
    if (!self::$hasMathJax) {
      foreach (self::MATHJAX_PATTERNS as $pat) {
        self::$hasMathJax |= preg_match($pat, $s);
      }
    }
  }

  static function hasMathJax(): bool {
    return self::$hasMathJax;
  }

  // Parses and prints a textblock. Use this to insert dynamic textblocks
  // inside static templates / views.
  static function include($page_name, $template_args = null, $div = true) {
    $textblock = textblock_get_revision($page_name);
    log_assert($textblock, "Nu am găsit $page_name");

    if ($div) {
      echo '<div class="wiki_text_block">';
    }
    if (!is_null($template_args)) {
      textblock_template_replace($textblock, $template_args);
      // No caching, we're using template magic.
      echo self::processText($textblock['text']);
    } else {
      echo self::processTextblock($textblock);
    }
    if ($div) {
      echo '</div>';
    }
  }

  // Process Textile (and macros) and returns the HTML string.
  static function processTextile(string $content): string {
    self::checkForMathJax($content);
    $weaver = new MyTextile();
    $res = $weaver->parse($content);
    unset($weaver);

    return $res;
  }

  private static function macroCallback(array $matches): string {
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
    return execute_macro($macro_name, $macro_args);
  }

  // Proces macros in content.
  private static function processOnlyMacros(string $content): string {
    $res = preg_replace_callback(
      '/ <span \s* macro_name="([a-z][a-z0-9_]*)" \s* runas="macro" \s*
                ((?: (?:[a-z][a-z0-9_]*) \s* = \s*
                    "(?:(?:[^"]*(?:"")*)*)" \s* )* \s*)
                ><\/span>/xi', [ 'Wiki', 'macroCallback'], $content);
    return $res ?? '';
  }

  // No caching, used by JSON and others
  // Transforms textile into full html with no cache.
  // There is no $tb object in JSON, so we're sort of fucked.
  static function processText(string $content): string {
    return self::processOnlyMacros(self::processTextile($content));
  }

  // This processes a big chunk of wiki-formatted text and returns html.
  // Note: receives full textblock, not only $text.
  // NOTE: Caching does not work with templated textblocks. They suck.
  static function processTextblock(array $tb): string {
    log_assert_valid(textblock_validate($tb));

    if (!IA_TEXTILE_CACHE_ENABLE) {
      return self::processText($tb['text']);
    } else {
      $cache_id = preg_replace('/[^a-z0-9\.\-_]/i', '_', $tb['name']) . '_' .
        db_date_parse($tb['timestamp']);
      $cache_res = disk_cache_get($cache_id);
      if ($cache_res == false) {
        $cache_res = self::processTextile($tb['text']);
        disk_cache_set($cache_id, $cache_res);
      } else {
        self::checkForMathJax($cache_res);
      }
      return self::processOnlyMacros($cache_res);
    }
  }

  private static function processTextblockRecursiveHelper(
    array $textblock, bool $cache = true): string {
    log_assert_valid(textblock_validate($textblock));

    if (self::$recursionDepth > self::MAX_RECURSIVE_INCLUDES) {
      $msg = sprintf('Textile evaluation stopped because the recursive ' .
                     'include limit is %d.', self::MAX_RECURSIVE_INCLUDES);
      $div = sprintf('<div class="rejected-textile">%s</div>', $msg);
      return $div;
    }

    if ($cache) {
      $res = self::processTextblock($textblock);
    } else {
      $res = self::processText($textblock['text']);
    }

    return $res;
  }

  // This is just like processText, but it's meant for recursive calling. You
  // should use this from macros that include other text blocks.
  //
  // This returns a html block. That html block can be an error div.
  // You can set $cache to false to disable caching, mainly for templates.
  static function processTextblockRecursive(
    array $textblock, bool $cache = true): string {

    self::$recursionDepth++;
    $result = self::processTextblockRecursiveHelper($textblock, $cache);
    self::$recursionDepth--;

    return $result;
  }
}
