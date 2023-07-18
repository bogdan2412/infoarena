<?php

/**
 * Wrapper around Smarty, the template engine.
 */

require_once 'third-party/smarty-4.3.0/Smarty.class.php';
require_once __DIR__ . '/../www/format/format.php';

class Smart {
  private static $theSmarty = null;
  private static $includedResources = [];

  const RESOURCE_MAP = [
    'foreach' => [
      'js' => [ 'js/third-party/foreach.js' ],
    ],
    'highlight' => [
      'css' => [ 'css/third-party/highlight-theme.css' ],
      'js' => [
        'js/third-party/highlight.pack.js',
        'js/third-party/highlight-line-numbers.min.js',
      ],
    ],
    'iconize' => [
      'css' => [ 'css/third-party/iconize-0.5/iconize.css' ],
    ],
    'jquery' => [
      'js' => [ 'js/third-party/jquery-3.7.0.min.js' ],
    ],
    'main' => [
      'css' => [ 'css/sitewide.css', 'css/screen.css', 'css/print.css' ],
      'js' => [
        'js/config.js.php', 'js/default.js', 'js/postdata.js',
        'js/roundtimer.js', 'js/submit.js', 'js/tags.js', 'js/time.js',
      ],
      'deps' => [
        'foreach', 'highlight', 'iconize', 'jquery', 'sorttable', 'tabber',
        'tablednd',
      ],
    ],
    'monitor' => [
      'js' => [ 'js/monitor.js' ],
      'deps' => [ 'jquery' ],
    ],
    'sorttable' => [
      'js' => [ 'js/third-party/sorttable.js' ],
    ],
    'tabber' => [
      'css' => [ 'css/third-party/tabber.css' ],
      'js' => [ 'js/third-party/tabber-minimized.js' ],
    ],
    'tablednd' => [
      'js' => [ 'js/third-party/tablednd.js' ],
    ],
  ];

  static function init(): void {
    $s = new Smarty();
    $s->template_dir = Config::ROOT . 'templates';
    $s->compile_dir = sys_get_temp_dir() . '/templates_c';
    $s->registerPlugin('modifier', 'fullDateTime', 'Time::fullDateTime');
    self::$theSmarty = $s;
  }

  private static function collectResourcesWithDeps(): array {
    // first add all dependencies
    $map = [];
    while ($key = array_pop(self::$includedResources)) {
      $map[$key] = true;
      $deps = self::RESOURCE_MAP[$key]['deps'] ?? [];
      foreach ($deps as $dep) {
        if (!isset($map[$dep])) {
          self::$includedResources[] = $dep;
        }
      }
    }

    // now collect CSS and JS files in map order
    $resultCss = [];
    $resultJs = [];
    foreach (self::RESOURCE_MAP as $key => $data) {
      if (isset($map[$key])) {
        $list = $data['css'] ?? [];
        array_push($resultCss, ...$list);

        $list = $data['js'] ?? [];
        array_push($resultJs, ...$list);
      }
    }

    return [ $resultCss, $resultJs ];
  }

  // Marks required CSS and JS files for inclusion.
  // $keys: array of keys in self::RESOURCE_MAP
  static function addResources(...$keys): void {
    foreach ($keys as $key) {
      if (!isset(self::RESOURCE_MAP[$key])) {
        FlashMessage::addError("Unknown resource ID {$key}");
        redirect(url_home());
      }
      self::$includedResources[] = $key;
    }
  }

  /**
   * Can be called as
   * assign($name, $value) or
   * assign([$name1 => $value1, $name2 => $value2, ...])
   **/
  static function assign($arg1, $arg2 = null): void {
    if (is_array($arg1)) {
      foreach ($arg1 as $name => $value) {
        self::$theSmarty->assign($name, $value);
      }
    } else {
      self::$theSmarty->assign($arg1, $arg2);
    }
  }

  // Display a single template without the layout. Used during the migration
  // to Smarty.
  static function displayBit(string $templateName): void {
    print self::$theSmarty->fetch($templateName);
  }

  /* Prepare and display a template. */
  static function display(string $templateName): void {
    self::addResources('main');
    print self::fetch($templateName);
  }

  static function fetch(string $templateName): string {
    list ($cssFiles, $jsFiles) = self::collectResourcesWithDeps();
    $cssFiles = self::makeRelativeUrls($cssFiles);
    $jsFiles = self::makeRelativeUrls($jsFiles);
    $identity = Identity::get();

    $ratingBadge = $identity
      ? new RatingBadge($identity->username, $identity->rating_cache)
      : null;

    self::assign([
      'cssFiles' => $cssFiles,
      'currentYear' => date('Y'),
      'jsFiles' => $jsFiles,
      'identity' => $identity,
      'ratingBadge' => $ratingBadge,
    ]);
    return self::$theSmarty->fetch($templateName);
  }

  private static function makeRelativeUrls(array $urls): array {
    $result = [];
    foreach ($urls as $url) {
      $result[] = url_static($url);
    }
    return $result;
  }
}
